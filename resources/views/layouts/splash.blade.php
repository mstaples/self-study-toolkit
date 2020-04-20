@include('component.head')
<body class="homepage is-preload">
<div id="page-wrapper">

    <!-- Header -->
    <section id="header">

        <!-- Logo -->
        <h1><a href="{{ '/' }}">@yield('title')</a></h1>

        <!-- Nav -->
        <nav id="nav">
            @include('component.navbar')
        </nav>

        <!-- Banner -->
        <section id="banner">
            <header>
                @include('component.banner')
            </header>
        </section>

        <!-- Intro -->
        <section id="intro" class="container">
            <div class="row">
                @include('component.cards')
            </div>
            <footer>
                <ul class="actions">
                    @include('component.buttons')
                </ul>
            </footer>
        </section>

    </section>

    <!-- Main -->
    <section id="main">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    @include('component.gallery')
                </div>
                <div class="col-12">
                    @include('component.features')
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <section id="footer">
        @include('component.footer')
    </section>

</div>
@include('component.tail')
