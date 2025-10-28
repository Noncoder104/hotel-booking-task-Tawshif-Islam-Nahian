<!DOCTYPE html>
<html>
<head><title>Book a Room</title></head>
<body>
    <h1>Hotel Booking System - Find Rooms</h1>
    
    @if ($errors->any())
        <div style="color: red; border: 1px solid red; padding: 10px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('booking.search') }}">
        @csrf
        <h2>1. Your Details</h2>
        <label>Name: <input type="text" name="user_name" required value="{{ old('user_name') }}"></label><br><br>
        <label>Email: <input type="email" name="email" required value="{{ old('email') }}" placeholder="Must be valid"></label><br>
        <label>Phone: <input type="text" name="phone" required value="{{ old('phone') }}" placeholder="Must be valid"></label><br><br>

        <h2>2. Booking Dates</h2>
        <label>From Date (Check-in): <input type="date" name="from_date" required min="{{ date('Y-m-d') }}" value="{{ old('from_date') }}"></label><br>
        <label>To Date (Check-out): <input type="date" name="to_date" required min="{{ date('Y-m-d', strtotime('+1 day')) }}" value="{{ old('to_date') }}"></label><br><br>

        <button type="submit">Search Available Rooms</button>
    </form>
</body>
</html>