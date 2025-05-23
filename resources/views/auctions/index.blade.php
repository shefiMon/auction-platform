<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Live Auctions') }}
            </h2>

        </div>

            @auth

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.auctions.create') }}" class="bg-blue-500 hover:bg-blue-700 text-blue font-bold py-2 px-4 rounded">
                        Create Auction
                    </a>
                @endif
            @endauth
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($auctions as $auction)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        @if($auction->image)
                            <img src="{{ Storage::url($auction->image) }}" alt="{{ $auction->title }}" class="w-full h-48 object-cover">
                        @endif

                        <div class="p-6">
                            <h3 class="font-semibold text-lg mb-2">{{ $auction->title }}</h3>
                            <p class="text-gray-600 mb-4">{{ Str::limit($auction->description, 100) }}</p>

                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Current Price:</span>
                                    <span class="font-bold text-green-600">${{ number_format($auction->current_price, 2) }}</span>
                                </div>

                                @if($auction->isActive())
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Time Remaining:</span>
                                        <span class="font-bold text-red-600" id="timer-{{ $auction->id }}">
                                            {{ gmdate('H:i:s', $auction->timeRemaining()) }}
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
                                <a href="{{ route('auctions.show', $auction) }}" class="w-full bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
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
        setInterval(function() {
            @foreach($auctions as $auction)
                @if($auction->isActive())
                    const timer{{ $auction->id }} = document.getElementById('timer-{{ $auction->id }}');
                    const endTime = new Date('{{ $auction->end_time->toISOString() }}');
                    const now = new Date();
                    const diff = Math.max(0, endTime - now);

                    const hours = Math.floor(diff / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                    if (timer{{ $auction->id }}) {
                        timer{{ $auction->id }}.textContent =
                            hours.toString().padStart(2, '0') + ':' +
                            minutes.toString().padStart(2, '0') + ':' +
                            seconds.toString().padStart(2, '0');
                    }
                @endif
            @endforeach
        }, 1000);
    </script>
    @endpush
</x-app-layout>
