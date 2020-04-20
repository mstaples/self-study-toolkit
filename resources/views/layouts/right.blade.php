@include('component.head')
<body class="right-sidebar is-preload">
<div id="page-wrapper">

    <!-- Header -->
    <section id="header">

        <!-- Logo -->
        <h1><a href="index.html">Dopetrope</a></h1>

        <!-- Nav -->
        <nav id="nav">
            @include('component.navbar')
        </nav>

    </section>

    <!-- Main -->
    <section id="main">
        <div class="container">
            <div class="row">
                <div class="col-8 col-12-medium">

                    <!-- Content -->
                    <article class="box post">
                        <a href="#" class="image featured"><img src="{{asset('images/pic01.jpg')}}" alt="" /></a>
                        <header>
                            <h2>Right sidebar</h2>
                            <p>Lorem ipsum dolor sit amet feugiat</p>
                        </header>
                        <p>
                            Vestibulum scelerisque ultricies libero id hendrerit. Vivamus malesuada quam faucibus ante dignissim auctor
                            hendrerit libero placerat. Nulla facilisi. Proin aliquam felis non arcu molestie at accumsan turpis commodo.
                            Proin elementum, nibh non egestas sodales, augue quam aliquet est, id egestas diam justo adipiscing ante.
                            Pellentesque tempus nulla non urna eleifend ut ultrices nisi faucibus.
                        </p>
                        <p>
                            Praesent a dolor leo. Duis in felis in tortor lobortis volutpat et pretium tellus. Vestibulum ac ante nisl,
                            a elementum odio. Duis semper risus et lectus commodo fringilla. Maecenas sagittis convallis justo vel mattis.
                            placerat, nunc diam iaculis massa, et aliquet nibh leo non nisl vitae porta lobortis, enim neque fringilla nunc,
                            eget faucibus lacus sem quis nunc suspendisse nec lectus sit amet augue rutrum vulputate ut ut mi. Aenean
                            elementum, mi sit amet porttitor lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor
                            sit amet nullam consequat feugiat dolore tempus.
                        </p>
                        <section>
                            <header>
                                <h3>Something else</h3>
                            </header>
                            <p>
                                Elementum odio duis semper risus et lectus commodo fringilla. Maecenas sagittis convallis justo vel mattis.
                                placerat, nunc diam iaculis massa, et aliquet nibh leo non nisl vitae porta lobortis, enim neque fringilla nunc,
                                eget faucibus lacus sem quis nunc suspendisse nec lectus sit amet augue rutrum vulputate ut ut mi. Aenean
                                elementum, mi sit amet porttitor lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor
                                sit amet nullam consequat feugiat dolore tempus.
                            </p>
                            <p>
                                Nunc diam iaculis massa, et aliquet nibh leo non nisl vitae porta lobortis, enim neque fringilla nunc,
                                eget faucibus lacus sem quis nunc suspendisse nec lectus sit amet augue rutrum vulputate ut ut mi. Aenean
                                elementum, mi sit amet porttitor lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor
                                sit amet nullam consequat feugiat dolore tempus.
                            </p>
                        </section>
                        <section>
                            <header>
                                <h3>So in conclusion ...</h3>
                            </header>
                            <p>
                                Nunc diam iaculis massa, et aliquet nibh leo non nisl vitae porta lobortis, enim neque fringilla nunc,
                                eget faucibus lacus sem quis nunc suspendisse nec lectus sit amet augue rutrum vulputate ut ut mi. Aenean
                                elementum, mi sit amet porttitor lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor
                                sit amet nullam consequat feugiat dolore tempus. Elementum odio duis semper risus et lectus commodo fringilla.
                                Maecenas sagittis convallis justo vel mattis.
                            </p>
                        </section>
                    </article>

                </div>
                <div class="col-4 col-12-medium">

                    <!-- Sidebar -->
                    <section class="box">
                        <a href="#" class="image featured"><img src="{{asset('images/pic09.jpg')}}" alt="" /></a>
                        <header>
                            <h3>Sed etiam lorem nulla</h3>
                        </header>
                        <p>Lorem ipsum dolor sit amet sit veroeros sed amet blandit consequat veroeros lorem blandit  adipiscing et feugiat phasellus tempus dolore ipsum lorem dolore.</p>
                        <footer>
                            <a href="#" class="button alt">Magna sed taciti</a>
                        </footer>
                    </section>
                    <section class="box">
                        <header>
                            <h3>Feugiat consequat</h3>
                        </header>
                        <p>Veroeros sed amet blandit consequat veroeros lorem blandit adipiscing et feugiat sed lorem consequat feugiat lorem dolore.</p>
                        <ul class="divided">
                            <li><a href="#">Sed et blandit consequat sed</a></li>
                            <li><a href="#">Hendrerit tortor vitae sapien dolore</a></li>
                            <li><a href="#">Sapien id suscipit magna sed felis</a></li>
                            <li><a href="#">Aptent taciti sociosqu ad litora</a></li>
                        </ul>
                        <footer>
                            <a href="#" class="button alt">Ipsum consequat</a>
                        </footer>
                    </section>

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
