<!DOCTYPE html>
<html>
<head><title>Room Results</title></head>
<body>
    <h1>Available Rooms for {{ $bookingData['from_date'] }} to {{ $bookingData['to_date'] }}</h1>

    @if ($errors->any())
        <div style="color: red;">{{ $errors->first() }}</div>
    @endif

    @forelse ($categories as $category)
        <div style="border: 2px solid #ccc; padding: 15px; margin-bottom: 20px;">
            <h3>{{ $category->name }}</h3>
            <p>Base Price: **{{ number_format($category->base_price) }} BDT**</p>
            
            @if ($category->is_available)
                <p style="color: green; font-weight: bold;">Available! ✅</p>

                <p>Nights: {{ $category->pricing['nights'] }}</p>
                <p>Total Base Price: {{ number_format($category->pricing['total_base_price']) }} BDT</p>
                <p>
                    **FINAL PRICE:** <span style="font-size: 1.2em; color: darkgreen;">**{{ number_format($category->pricing['final_price']) }} BDT**</span>
                    (Includes rules)
                </p>

                <form method="POST" action="{{ route('booking.store') }}">
                    @csrf
                    <input type="hidden" name="user_name" value="{{ $bookingData['user_name'] }}">
                    <input type="hidden" name="email" value="{{ $bookingData['email'] }}">
                    <input type="hidden" name="phone" value="{{ $bookingData['phone'] }}">
                    <input type="hidden" name="from_date" value="{{ $bookingData['from_date'] }}">
                    <input type="hidden" name="to_date" value="{{ $bookingData['to_date'] }}">
                    <input type="hidden" name="room_category_id" value="{{ $category->id }}">
                    
                    <button type="submit" style="padding: 10px; background-color: blue; color: white;">Confirm Booking</button>
                </form>
            @else
                <p style="color: red; font-weight: bold;">No room available. 😞</p>
            @endif
        </div>
    @empty
        <p>No room categories found.</p>
    @endforelse
</body>
</html>