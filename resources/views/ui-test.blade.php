@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h1 class="text-3xl font-bold text-blue-600 mb-4">UI Test Page</h1>
        
        <div class="space-y-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                <strong>Success!</strong> If you can see this styled message, Tailwind CSS is working.
            </div>
            
            <div class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 inline-block cursor-pointer" onclick="testJS()">
                Click me to test JavaScript
            </div>
            
            <div id="js-result" class="hidden bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                JavaScript is working!
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-red-100 p-4 rounded">
                    <h3 class="font-bold text-red-800">Card 1</h3>
                    <p class="text-red-600">Test responsive grid</p>
                </div>
                <div class="bg-green-100 p-4 rounded">
                    <h3 class="font-bold text-green-800">Card 2</h3>
                    <p class="text-green-600">Tailwind utility classes</p>
                </div>
                <div class="bg-blue-100 p-4 rounded">
                    <h3 class="font-bold text-blue-800">Card 3</h3>
                    <p class="text-blue-600">CSS Grid layout</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testJS() {
    document.getElementById('js-result').classList.remove('hidden');
    console.log('JavaScript test executed successfully!');
}

// Log asset loading info
console.log('UI Test Page loaded');
console.log('Location:', window.location.href);
console.log('Document ready state:', document.readyState);
</script>
@endsection
