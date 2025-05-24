<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Live Auctions') }}
            </h2>

        </div>


    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="auctions-grid">
                @forelse($auctions as $auction)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="relative">
                            @if ($auction->image)
                                <img src="{{ Storage::url($auction->image) }}" alt="{{ $auction->title }}"
                                    class="w-full h-48 object-cover">
                            @endif
                            @if ($auction->isActive())
                                <span
                                    class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                                    Live
                                </span>
                            @endif
                        </div>

                        <div class="p-6">
                            <h3 class="font-semibold text-lg mb-2">{{ $auction->title }}</h3>
                            <p class="text-gray-600 mb-4">{{ Str::limit($auction->description, 100) }}</p>

                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Current Price:</span>
                                    <span
                                        class="font-bold text-green-600">${{ number_format($auction->current_price, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Starts At:</span>
                                    <span class="text-sm">{{ $auction->start_time->format('M d, Y H:i') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Ends At:</span>
                                    <span class="text-sm">{{ $auction->end_time->format('M d, Y H:i') }}</span>
                                </div>
                                @if ($auction->isActive())
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Time Left:</span>
                                        <span class="font-bold text-red-600" id="timer-{{ $auction->id }}">
                                            {{-- {{ gmdate('H:i:s', $auction->timeRemaining()) }} --}}
                                        </span>
                                    </div>
                                @else
                                    <div class="text-center">
                                        <span class="px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm">
                                            {{ ucfirst($auction->status) }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('auctions.show', $auction) }}"
                                    class="w-full bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    View Auction
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12">
                        <p class="text-gray-500">No active auctions at the moment.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $auctions->links() }}
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Update countdown timers

            document.addEventListener('DOMContentLoaded', function() {
                // Check if Echo is properly initialized
                if (typeof window.Echo === 'undefined') {
                    console.error('Echo is not initialized. Check your bootstrap.js and Pusher configuration.');
                    return;
                }
                // Listen for auction created event
                window.Echo.channel('auction.created')
                    .listen('NewAuction', (event) => {
                        console.log('New auction created:', event);
                        addAuction(event);

                    });
            });


            function updateTimer(timerId, endTimeStr) {
                const timerElement = document.getElementById(timerId);
                if (!timerElement) return;

                const endTime = new Date(endTimeStr);
                const now = new Date();
                const diff = Math.max(0, endTime - now);

                if (diff === 0) {
                    timerElement.textContent = 'Auction ended';
                    return;
                }

                const hours = Math.floor(diff / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                const days = Math.floor(hours / 24);
                const remainingHours = hours % 24;
                timerElement.textContent =
                    (days > 0 ? days + 'd ' : '') +
                    remainingHours.toString().padStart(2, '0') + ':' +
                    minutes.toString().padStart(2, '0') + ':' +
                    seconds.toString().padStart(2, '0');
            }

            setInterval(function() {
                @foreach ($auctions as $auction)
                    @if ($auction->isActive())
                        updateTimer('timer-{{ $auction->id }}', '{{ $auction->end_time->toISOString() }}');
                    @endif
                @endforeach
            }, 1000);

            function addAuction(auction) {
                const auctionsGrid = document.querySelector('#auctions-grid');

                const auctionCard = `
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="relative">
                            ${auction.image ?
                                `<img src="/storage/${auction.image}" alt="${auction.title}" class="w-full h-48 object-cover">`
                                : ''}
                            <span class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                                Live
                            </span>
                        </div>
                        <div class="p-6">
                            <h3 class="font-semibold text-lg mb-2">${auction.title}</h3>
                            <p class="text-gray-600 mb-4">${auction.description.substring(0, 100)}...</p>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Current Price:</span>
                                    <span class="font-bold text-green-600">$${parseFloat(auction.current_price).toFixed(2)}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Time Left:</span>
                                    <span class="font-bold text-red-600" id="timer-${auction.id}"></span>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="/auctions/${auction.id}" class="w-full bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    View Auction
                                </a>
                            </div>
                        </div>
                    </div>
                `;

                auctionsGrid.insertAdjacentHTML('beforeend', auctionCard);
                // auctionsGrid.insertAdjacentHTML('afterbegin', auctionCard);
            }
        </script>
    @endpush
</x-app-layout>
