<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\ReviewModel;
use App\Models\BookingModel;
use App\Libraries\GeminiClient;

class Review extends BaseController
{
    protected $reviewModel;
    protected $bookingModel;
    protected $geminiClient;

    public function __construct()
    {
        $this->reviewModel  = new ReviewModel();
        $this->bookingModel = new BookingModel();
        $this->geminiClient  = new GeminiClient();
        helper(['form', 'url']);
    }

    public function store()
    {
        $rules = [
            'booking_id' => 'required|numeric',
            'rating'     => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[5]',
            'comment'    => 'permit_empty|string|max_length[1000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', 'Validasi ulasan gagal. Periksa kembali rating dan komentar Anda.');
        }

        $bookingId = $this->request->getPost('booking_id');
        $rating    = (int) $this->request->getPost('rating');
        $comment   = trim($this->request->getPost('comment') ?? '');

        // Verify booking ownership
        $booking = $this->bookingModel->find($bookingId);
        if (!$booking || $booking['user_id'] != session()->get('userId')) {
            return redirect()->back()->with('error', 'Akses ulasan tidak sah.');
        }

        // Verify if already reviewed
        $existing = $this->reviewModel->where('booking_id', $bookingId)->first();
        if ($existing) {
            return redirect()->back()->with('error', 'Anda sudah memberikan ulasan untuk tiket ini.');
        }

        // Default sentiment analysis fallback (Rule-based)
        $sentiment = 'neutral';
        if ($rating >= 4) {
            $sentiment = 'positive';
        } elseif ($rating <= 2) {
            $sentiment = 'negative';
        }

        // Perform AI Sentiment Analysis using Gemini if comment is not empty
        if (!empty($comment)) {
            $prompt = "Tolong analisis sentimen dari komentar ulasan penumpang bus berikut ini:\n"
                . "\"{$comment}\"\n\n"
                . "PILIHAN RESPON: Anda HARUS hanya menjawab dengan satu kata lowercase dari tiga pilihan berikut: 'positive', 'neutral', atau 'negative'. Jangan sertakan tanda baca atau kata tambahan lain.";
            
            $systemInstruction = "Anda adalah sistem klasifikasi sentimen otomatis untuk ulasan pelanggan SiTeBus.";

            $aiSentiment = strtolower(trim($this->geminiClient->generate($prompt, $systemInstruction)));

            // Validate response and clean up in case of extra spaces/newlines
            if (in_array($aiSentiment, ['positive', 'neutral', 'negative'])) {
                $sentiment = $aiSentiment;
            }
        }

        $data = [
            'booking_id' => $bookingId,
            'user_id'    => session()->get('userId'),
            'rating'     => $rating,
            'comment'    => $comment,
            'sentiment'  => $sentiment
        ];

        if ($this->reviewModel->save($data)) {
            return redirect()->to(base_url('customer/home'))->with('success', 'Ulasan Anda berhasil dikirim! Analisis Sentimen AI: ' . ucfirst($sentiment));
        }

        return redirect()->back()->with('error', 'Gagal mengirimkan ulasan.');
    }
}
