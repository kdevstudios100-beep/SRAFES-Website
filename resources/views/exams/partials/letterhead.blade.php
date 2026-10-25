{{-- School emblem and name for printed exam pages; $title is the line under the name --}}
<header class="letterhead">
    <img src="{{ asset('images/logo-emblem.png') }}" alt="" width="84" height="72">
    <div>
        <h1>{{ config('srafes.exams.school_name') }}</h1>
        <p>{{ $title }}</p>
    </div>
</header>
