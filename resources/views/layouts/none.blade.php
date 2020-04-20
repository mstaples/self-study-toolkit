@include('component.head')
<body class="no-sidebar is-preload">
<div id="page-wrapper">

    <!-- Header -->
    <section id="header">

        <!-- Logo -->
        <h1>@yield('title')</h1>

        <!-- Nav -->
        <nav id="nav">
            @include('component.navbar')
        </nav>

    </section>

    <!-- Main -->
    <section id="main">
        <div class="container">

            <!-- Content -->
            <article class="box post">
                <a href="#" class="image featured"><img src="{{asset('images/pic01.jpg')}}" alt="" /></a>
                <header>
                    <h2>Empty</h2>
                    <p>Lorem ipsum dolor sit amet feugiat</p>
                </header>
            </article>

        </div>
    </section>

    <!-- Footer -->
    <section id="footer">
        @include('component.footer')
    </section>

</div>
@include('component.tail')
