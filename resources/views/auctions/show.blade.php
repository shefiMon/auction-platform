<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Auction Details -->
                        <div>
                            @if($auction->image)
                                <img src="{{ Storage::url($auction->image) }}" alt="{{ $auction->title }}" class="w-full h-64 object-cover rounded-lg mb-4">
                            @endif

                            <!-- Live Stream -->
                            @if($auction->stream_url && $auction->isActive())
                                <div class="mb-4">
                                    <h3 class="font-semibold text-lg mb-2">Live Stream</h3>
                                    <div class="aspect-w-16 aspect-h-9">
                                        <iframe src="{{ $auction->stream_url }}" class="w-full h-64 rounded-lg"></iframe>
                                    </div>
                                </div>
                            @endif

                            <h1 class="text-3xl font-bold mb-4">{{ $auction->title }}</h1>
                            <p class="text-gray-700 mb-6">{{ $auction->description }}</p>

                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Starting Price:</span>
                                    <span class="font-semibold">${{ number_format($auction->starting_price, 2) }}</span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-gray-600">Current Price:</span>
                                    <span class="font-bold text-2xl text-green-600" id="current-price">
                                        ${{ number_format($auction->current_price, 2) }}
                                    </span>
                                </div>

                                @if($auction->reserve_price)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Reserve Price:</span>
                                        <span class="font-semibold">${{ number_format($auction->reserve_price, 2) }}</span>
                                    </div>
                                @endif

                                @if($auction->isActive())
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Time Remaining:</span>
                                        <span class="font-bold text-2xl text-red-600" id="countdown-timer">
                                            {{ gmdate('H:i:s', $auction->timeRemaining()) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Bidding Panel -->
                        <div>
                            @if($auction->isActive() && auth()->check() && !auth()->user()->isAdmin())
                                <div class="bg-gray-50 p-6 rounded-lg mb-6">
                                    <h3 class="font-semibold text-lg mb-4">Place Your Bid</h3>

                                    <form action="{{ route('auctions.bid', $auction) }}" method="POST" id="bid-form">
                                        @csrf
                                        <div class="mb-4">
                                            <label for="amount" class="block text-sm font-medium text-gray-700">Bid Amount</label>
                                            <input type="number"
                                                   name="amount"
                                                   id="amount"
                                                   min="{{ $auction->current_price + 1 }}"
                                                   step="0.01"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                   required>
                                            @error('amount')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg">
                                            Place Bid
                                        </button>
                                    </form>

                                    <p class="text-sm text-gray-500 mt-2">
                                        Your balance: ${{ number_format(auth()->user()->balance, 2) }}
                                    </p>
                                </div>
                            @endif

                            <!-- Recent Bids -->
                            <div class="bg-gray-50 p-6 rounded-lg mb-6">
                                <h3 class="font-semibold text-lg mb-4">Recent Bids</h3>
                                <div id="bids-list" class="space-y-2 max-h-64 overflow-y-auto">
                                    @forelse($auction->bids->take(10) as $bid)
                                        <div class="flex justify-between items-center py-2 border-b">
                                            <span class="font-medium">{{ $bid->user->name }}</span>
                                            <div class="text-right">
                                                <div class="font-bold text-green-600">${{ number_format($bid->amount, 2) }}</div>
                                                <div class="text-xs text-gray-500">{{ $bid->bid_time->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-gray-500">No bids yet. Be the first to bid!</p>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Chat Section -->
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h3 class="font-semibold text-lg mb-4">Live Chat</h3>

                                <div id="chat-messages" class="space-y-2 max-h-64 overflow-y-auto mb-4 p-3 bg-white rounded border">
                                    @foreach($auction->chatMessages as $message)
                                        <div class="text-sm">
                                            <span class="font-medium text-blue-600">{{ $message->user->name }}:</span>
                                            <span class="text-gray-700">{{ $message->message }}</span>
                                            <span class="text-xs text-gray-400 ml-2">{{ $message->created_at->format('H:i') }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                @auth
                                    <form id="chat-form" class="flex">
                                        @csrf
                                        <input type="text"
                                               id="chat-message"
                                               placeholder="Type your message..."
                                               class="flex-1 rounded-l-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                               maxlength="500"
                                               required>
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-r-md">
                                            Send
                                        </button>
                                    </form>
                                @else
                                    <p class="text-gray-500 text-center">Please login to participate in chat.</p>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Initialize Echo for real-time updates
        window.Echo.channel('auction.{{ $auction->id }}')
            .listen('NewBid', (e) => {
                // Update current price
                document.getElementById('current-price').textContent = ' + parseFloat(e.auction.current_price).toFixed(2);

                // Update minimum bid amount
                const amountInput = document.getElementById('amount');
                if (amountInput) {
                    amountInput.min = parseFloat(e.auction.current_price) + 1;
                }

                // Add new bid to list
                const bidsList = document.getElementById('bids-list');
                const newBid = document.createElement('div');
                newBid.className = 'flex justify-between items-center py-2 border-b';
                newBid.innerHTML = `
                    <span class="font-medium">${e.bid.user_name}</span>
                    <div class="text-right">
                        <div class="font-bold text-green-600">${parseFloat(e.bid.amount).toFixed(2)}</div>
                        <div class="text-xs text-gray-500">Just now</div>
                    </div>
                `;
                bidsList.insertBefore(newBid, bidsList.firstChild);

                // Remove old bids if more than 10
                while (bidsList.children.length > 10) {
                    bidsList.removeChild(bidsList.lastChild);
                }

                // Update end time if extended
                updateCountdown(e.auction.end_time);
            });

        // Listen for chat messages
        window.Echo.channel('auction.{{ $auction->id }}.chat')
            .listen('NewChatMessage', (e) => {
                const chatMessages = document.getElementById('chat-messages');
                const messageDiv = document.createElement('div');
                messageDiv.className = 'text-sm';
                messageDiv.innerHTML = `
                    <span class="font-medium text-blue-600">${e.message.user_name}:</span>
                    <span class="text-gray-700">${e.message.message}</span>
                    <span class="text-xs text-gray-400 ml-2">Just now</span>
                `;
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            });

        // Handle chat form submission
        document.getElementById('chat-form')?.addEventListener('submit', function(e) {
            e.preventDefault();

            const messageInput = document.getElementById('chat-message');
            const message = messageInput.value.trim();

            if (!message) return;

            fetch('{{ route("auctions.chat", $auction) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ message: message })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageInput.value = '';
                }
            })
            .catch(error => console.error('Error:', error));
        });

        // Countdown timer
        let endTime = new Date('{{ $auction->end_time->toISOString() }}');

        function updateCountdown(newEndTime = null) {
            if (newEndTime) {
                endTime = new Date(newEndTime);
            }

            const timer = document.getElementById('countdown-timer');
            if (!timer) return;

            const now = new Date();
            const diff = Math.max(0, endTime - now);

            if (diff === 0) {
                timer.textContent = 'ENDED';
                timer.className = 'font-bold text-2xl text-gray-500';
                return;
            }

            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            timer.textContent =
                hours.toString().padStart(2, '0') + ':' +
                minutes.toString().padStart(2, '0') + ':' +
                seconds.toString().padStart(2, '0');
        }

        @if($auction->isActive())
            setInterval(updateCountdown, 1000);
        @endif
    </script>
    @endpush
</x-app-layout>
