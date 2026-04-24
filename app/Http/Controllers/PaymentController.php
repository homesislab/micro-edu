<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Invoice;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Show the checkout page for a premium course.
     */
    public function checkout(Course $course)
    {
        return Inertia::render('Payment/Checkout', [
            'course' => $course->load('expert', 'academy'),
        ]);
    }

    /**
     * Process the simulated payment.
     */
    public function process(Request $request, Course $course)
    {
        $user = auth()->user();

        // Check if already enrolled or has pending invoice
        if (Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->exists()) {
            return redirect()->route('dashboard')->with('info', 'Anda sudah terdaftar di kelas ini.');
        }

        // Calculate Revenue Split (10/90)
        $amount = $course->price;
        $platformFee = $amount * 0.10;
        $creatorRevenue = $amount - $platformFee;

        // Create Invoice
        $invoice = Invoice::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'academy_id' => $course->academy_id,
            'amount' => $amount,
            'platform_fee' => $platformFee,
            'creator_revenue' => $creatorRevenue,
            'status' => 'paid', // Automated "Instant Success" for Simulation
            'payment_gateway' => 'Simulated Gateway',
            'reference_id' => 'TRX-' . strtoupper(Str::random(10)),
            'paid_at' => now(),
        ]);

        // Complete Enrollment
        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        return redirect()->route('dashboard')->with('success', 'Pembayaran Berhasil! Kelas ' . $course->title . ' telah ditambahkan ke dashboard Anda.');
    }
}
