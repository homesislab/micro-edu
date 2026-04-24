<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Centralized method to "send" WhatsApp messages.
     * In a real app, this would call an API like Twilio, Waboxapp, etc.
     */
    public function sendMessage(User $user, string $message)
    {
        $payload = [
            'to' => $user->email, // Mocking phone with email for logic
            'message' => $message,
            'timestamp' => now()->toDateTimeString(),
        ];

        // LOG consistently for the "WA Bot" feature
        Log::channel('stack')->info("WHATSAPP_BOT_SEND", $payload);

        // We can also broadcast or save to notifications table if needed.
        return true;
    }

    public function sendWelcome(User $user, $courseTitle)
    {
        $userName = $user->name ?? 'Participant';
        $msg = "Halo {$userName}! 🌟 Selamat! Anda telah berhasil terdaftar di program '{$courseTitle}'. Kita akan belajar banyak hal seru! Sampai jumpa di sesi live!";
        return $this->sendMessage($user, $msg);
    }

    public function sendGraduation(User $user, $courseTitle)
    {
        $msg = "Selamat {$user->name}! 🎉 Anda telah dinyatakan LULUS dari program '{$courseTitle}'. Sertifikat Anda sudah tersedia di Dashboard ClassHub. Teruslah tumbuh!";
        return $this->sendMessage($user, $msg);
    }

    public function sendReviewReminder(User $user, int $pendingCount)
    {
        $msg = "Halo {$user->name}! 📋 Ada {$pendingCount} tugas di ClassHub yang sudah menunggu untuk diperiksa lebih dari 48 jam. Kecepatan respon Anda sangat berarti bagi proses belajar peserta! Yuk, cek dashboard expert hari ini.";
        return $this->sendMessage($user, $msg);
    }
}
