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
                            @if($auction->isActive())
                                <span class="inline-block bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold mb-4">
                                    Live
                                </span>
                            @else
                                <span class="inline-block bg-gray-300 text-gray-700 px-3 py-1 rounded-full text-xs font-bold mb-4">
                                    {{ ucfirst($auction->status) }}
                                </span>
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

                                 <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Starts At:</span>
                                    <span class="text-sm">{{ $auction->start_time->format('M d, Y H:i') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Ends At:</span>
                                    <span class="text-sm">{{ $auction->end_time->format('M d, Y H:i') }}</span>
                                </div>

                                @if($auction->isActive())
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Time Left:</span>
                                        <span class="font-bold text-2xl text-red-600" id="countdown-timer">
                                            {{-- {{ gmdate('H:i:s', $auction->timeRemaining()) }} --}}
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

                                    <form action="" method="POST" id="bid-form">
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
                                            <span class="text-xs text-gray-400 ml-2">{{ $message->created_at->diffForHumans() }}</span>
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

    document.addEventListener('DOMContentLoaded', function() {
            // Check if Echo is properly initialized
            if (typeof window.Echo === 'undefined') {
                console.error('Echo is not initialized. Check your bootstrap.js and Pusher configuration.');
                return;
            }

            // Initialize channels
            initializeChannels();

            // Setup chat form
            setupChatForm();

            // Initialize countdown if auction is active
            @if($auction->isActive())
                initializeCountdown();
            @endif
    });

    function initializeChannels() {
        try {
            // Auction channel for bids
            window.Echo.channel('auction.{{ $auction->id }}')
                .listen('NewBid', (e) => {
                    updateAuctionUI(e);
                });

            // Chat channel
            window.Echo.channel('auction.{{ $auction->id }}.chat')
                .listen('NewChatMessage', (e) => {
                    addChatMessage(e.message);
                });
        } catch (error) {
            console.error('Error initializing Echo channels:', error);
        }
    }

    function setupChatForm() {
        const chatForm = document.getElementById('chat-form');
        if (chatForm) {
            chatForm.addEventListener('submit', handleChatSubmit);
        }
    }

    function handleChatSubmit(event) {
        event.preventDefault();

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
    }

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
                hours.toString().padStart(2, '0') + ' hour ' +
                minutes.toString().padStart(2, '0') + ' min ' +
                seconds.toString().padStart(2, '0') + ' sec';
        }

        function initializeChannels() {
            try {
                // Auction channel for bids
                window.Echo.channel('auction.{{ $auction->id }}')
                    .listen('NewBid', (e) => {
                        updateAuctionUI(e);
                    });

                // Chat channel
                window.Echo.channel('auction.{{ $auction->id }}.chat')
                    .listen('NewChatMessage', (e) => {
                        console.log('New chat message received:', e.message);
                        addChatMessage(e.message);
                    });
            } catch (error) {
                console.error('Error initializing Echo channels:', error);
            }
        }

        function addChatMessage(message) {
            const chatMessages = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');

            // Function to check if a string contains only emoji
            const isEmoji  =/^[\u{1F600}-\u{1F64F}\u{1F300}-\u{1F5FF}\u{1F680}-\u{1F6FF}\u{1F700}-\u{1F77F}\u{1F780}-\u{1F7FF}\u{1F800}-\u{1F8FF}\u{1F900}-\u{1F9FF}\u{1FA00}-\u{1FA6F}\u{1FA70}-\u{1FAFF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}]+$/u.test(message.message.trim());


              messageDiv.className = `text-sm ${isEmoji ? 'emoji-message' : ''}`;

            messageDiv.innerHTML = `
                <span class="font-medium text-blue-600">${message.user_name}:</span>
                <span class="text-gray-700">${message.message}</span>
                <span class="text-xs text-gray-400 ml-2">${message.diffForHumans}</span>
            `;
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
              if (isEmoji) {
        setTimeout(() => {
            const emojiElement = document.createElement('div');
            emojiElement.className = 'emoji-fly';
            emojiElement.textContent = message.message;
            emojiElement.style.left = `${Math.random() * 80 + 10}%`;
            messageDiv.appendChild(emojiElement);

            // Remove the element after animation completes
            setTimeout(() => {
                emojiElement.remove();
            }, 2000);
        }, 100);
    }
        }

        function updateAuctionUI(e){
            console.log('New bid received:', e);

                // Update current price
              document.getElementById('current-price').textContent = `$${parseFloat(e.auction.current_price).toFixed(2)}`;

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
        }

        function initializeCountdown() {
            setInterval(updateCountdown, 1000);
        }

        // Handle bidding form submission
        document.getElementById('bid-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const amount = document.getElementById('amount').value;

            fetch('{{ route("auctions.bid", $auction) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ amount: amount })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Bid response:', data);
                if (data.success) {
                    document.getElementById('amount').value = '';
                } else {
                    alert(data.message || 'Error placing bid');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error placing bid');
            });
        });


        @if($auction->isActive())
            setInterval(updateCountdown, 1000);
        @endif
    </script>
    @endpush
</x-app-layout>
