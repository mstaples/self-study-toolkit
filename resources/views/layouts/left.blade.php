@include('component.head')
<body class="left-sidebar is-preload">
<div id="page-wrapper">
    <!-- Header -->
    <section id="header">
        <header>
            @section('heading')
                <h2>@yield('title')</h2>
            @show
            @include('component.navbar')
        </header>
    </section>

    <!-- Main -->
    <section id="main">
        @yield('open-main')
        <div class="container">
            <div class="row">
                <div class="col-4 col-12-medium">
                    @section('sidebar')
                    <!-- Sidebar -->
                    @yield('sidebar')
                    @show
                </div>
                <div class="col-8 col-12-medium imp-medium">

                    <!-- Content -->
                    <article class="box post">
                        @section('content')
                            @yield('content')
                        @show
                    </article>
                </div>
            </div>
        </div>
        @yield('close-main')
    </section>

    <!-- Footer -->
    <section id="footer">
        @include('component.footer')
    </section>

</div>
@include('component.tail')
