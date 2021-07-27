<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- <meta name="user" content="Auth::user()"> --}}

    <title>Ping Tool @yield('title')</title>

    <!-- Scripts -->
    <script src="{{ url(mix('js/app.js')) }}" defer></script>
    {{-- <script src="https://kit.fontawesome.com/a7527b94e9.js"></script> --}}

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

    <!-- Styles -->
    <link href="{{ url(mix('css/app.css')) }}" rel="stylesheet">
</head>
<body class="app header-fixed sidebar-fixed aside-menu-fixed sidebar-lg-show">
  <div id="app">
      @include('layouts.header')
      <div class="app-body">
          <main class="main">
              <div class="container-fluid">
                  <div class="animated fadeIn">
                      @yield('content')
                  </div>
              </div>
          </main>
      </div>
  </div>
</body>
<script>
  window.PusherKey = "{{ env('PUSHER_APP_KEY') }}";
  window.PusherCluster = "{{ env('PUSHER_APP_CLUSTER') }}";
  window.broadcasting = {
    broadcaster: 'pusher',
    key: "{{ env('PUSHER_APP_KEY') }}",
    cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
    encrypted: true
  }
</script>
@yield('scripts')
</html>
