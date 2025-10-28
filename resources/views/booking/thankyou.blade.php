<!DOCTYPE html>
<html>
<head><title>Thank You</title></head>
<body>
    <h1>Thank You! Booking Confirmed! 🎉</h1>
    <p>Your booking is confirmed, **{{ $booking->user_name }}**!</p>

    <h2>Booking Details</h2>
    <ul>
        <li>Room Category: **{{ $booking->category->name }}**</li>
        <li>Check-in: {{ $booking->from_date }}</li>
        <li>Check-out: {{ $booking->to_date }}</li>
        <li>Nights: {{ Carbon\Carbon::parse($booking->from_date)->diffInDays($booking->to_date) }}</li>
    </ul>

    <h2>Price Summary (Required on Thank You Page) [cite: 25]</h2>
    <p style="border: 1px solid #000; padding: 10px;">
        **Base Price (Pre-rule):** {{ number_format($booking->total_base_price) }} BDT<br>
        **Final Price (After rules/discounts):** <span style="font-size: 1.5em; color: darkred;">**{{ number_format($booking->final_price) }} BDT**</span>
    </p>

    <p>Next Steps: You will receive a confirmation email shortly.</p>
</body>
</html>