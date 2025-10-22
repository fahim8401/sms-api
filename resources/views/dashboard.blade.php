<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Welcome, {{ auth()->user()->name }}!</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-100 p-4 rounded">
                            <div class="text-sm text-gray-600">Balance</div>
                            <div class="text-2xl font-bold">৳{{ number_format(auth()->user()->balance, 2) }}</div>
                        </div>
                        <div class="bg-green-100 p-4 rounded">
                            <div class="text-sm text-gray-600">SMS Rate</div>
                            <div class="text-2xl font-bold">৳{{ number_format(auth()->user()->rate, 2) }}</div>
                        </div>
                        <div class="bg-purple-100 p-4 rounded">
                            <div class="text-sm text-gray-600">Role</div>
                            <div class="text-2xl font-bold">{{ ucfirst(auth()->user()->role) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">API Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Your API Key</label>
                            <div class="flex">
                                <input type="text" value="{{ auth()->user()->api_key }}" readonly 
                                       class="flex-1 rounded-l-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                       id="apiKey">
                                <button onclick="copyToClipboard('apiKey')" 
                                        class="bg-gray-200 px-4 py-2 rounded-r-md hover:bg-gray-300">
                                    Copy
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Send SMS API</label>
                            <input type="text" 
                                   value="{{ config('app.url') }}/api/smsapi2?api_key=YOUR_API_KEY&type=text&contacts=PHONE&senderid=SENDER&msg=MESSAGE" 
                                   readonly 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Check Balance API</label>
                            <input type="text" 
                                   value="{{ config('app.url') }}/api/getBalance?api_key=YOUR_API_KEY" 
                                   readonly 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Get DLR API</label>
                            <input type="text" 
                                   value="{{ config('app.url') }}/api/getDLR?message_id=MESSAGE_ID" 
                                   readonly 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function copyToClipboard(elementId) {
        const element = document.getElementById(elementId);
        element.select();
        document.execCommand('copy');
        alert('Copied to clipboard!');
    }
    </script>
</x-app-layout>
