@include('component.head')
<body class="no-sidebar is-preload">
<div id="page-wrapper">
    <!-- Main -->
    <section id="main">
        <div class="container">
            <!-- Content -->
            <article class="box post">
                @yield('content')
            </article>
        </div>
        @yield('close-main')
    </section>

    <!-- Footer -->
    <section id="footer">
        @include('component.footer')
    </section>

</div>
@include('component.tail')
