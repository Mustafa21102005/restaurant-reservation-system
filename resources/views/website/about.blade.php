@extends('layouts.theme')

@section('title', 'About Us')

@section('content')
    <article class="s-content">

        <section class="s-pageheader"
            style="background-image: url('{{ asset('theme/images/pageheader/pageheader-about-bg-3000.jpg') }}')">
            <div class="row">
                <div class="column xl-12 s-pageheader__content">
                    <h1 class="page-title">
                        Passion & Flavor
                    </h1>
                </div>
            </div>
        </section>

        <section class="s-pagecontent pagecontent">

            <div class="row width-narrower pageintro text-center">
                <div class="column xl-12">
                    <p class="lead">
                        Lorem ipsum dolor sit amet consectetur, adipisicing elit. Alias eos quas blanditiis, quos
                        sint nostrum fugit aperiam
                        inventore optio itaque molestias corporis, ipsa tenetur eligendi nihil iste porro, natus
                        culpa consequuntur? Maxime,
                        corporis tempore. Sed tenetur veritatis velit recusandae eum.
                    </p>
                </div>
            </div>

            <div class="row width-narrow content-block content-block--media-right">
                <div class="column content-block__text">
                    <h3>Where Passion Meets Flavor</h3>
                    <p>
                        Eaque temporibus culpa perferendis expedita assumenda mollitia magnam. Lorem ipsum dolor sit
                        amet consectetur adipisicing elit
                        facilis voluptates voluptatum animi numquam quas ea praesentium quaerat maxime sunt odit
                        inventore itaque est et autem sequi nulla.
                        Distinctio obcaecati nesciunt asperiores dolorum tenetur voluptates, nemo alias doloremque.
                    </p>
                </div>
                <div class="column content-block__media">
                    <figure>
                        <img src="{{ asset('theme/images/about/about-img-01.jpg') }}">
                    </figure>
                </div>
            </div>

            <div class="row width-narrow content-block content-block--media-left">
                <div class="column content-block__text">
                    <h3>A Dining Experience to Remember</h3>
                    <p>
                        Eaque temporibus culpa perferendis expedita assumenda mollitia magnam. Lorem ipsum dolor sit
                        amet consectetur adipisicing elit
                        facilis voluptates voluptatum animi numquam quas ea praesentium quaerat maxime sunt odit
                        inventore itaque est et autem sequi nulla.
                        Distinctio obcaecati nesciunt asperiores dolorum tenetur voluptates, nemo alias doloremque.
                    </p>
                    <p>
                        Voluptates voluptatum animi numquam quas ea praesentium quaerat maxime sunt odit
                        inventore itaque est et autem sequi nulla.
                    </p>
                </div>
                <div class="column content-block__media">
                    <figure>
                        <img src="{{ asset('theme/images/about/about-img-02.jpg') }}">
                    </figure>
                </div>
            </div>

            <div class="row width-narrow content-block content-block--media-right">
                <div class="column content-block__text">
                    <h3>Locally Inspired, Sustainably Sourced</h3>
                    <p>
                        Eaque temporibus culpa perferendis expedita assumenda mollitia magnam. Lorem ipsum dolor sit
                        amet consectetur adipisicing elit
                        facilis voluptates voluptatum animi numquam quas ea praesentium quaerat maxime sunt odit
                        inventore itaque est et autem sequi nulla.
                        Distinctio obcaecati nesciunt asperiores dolorum tenetur voluptates, nemo alias doloremque.
                    </p>
                </div>
                <div class="column content-block__media">
                    <figure>
                        <img src="{{ asset('theme/images/about/about-img-03.jpg') }}">
                    </figure>
                </div>
            </div>

            <div class="row width-narrow content-block content-block--grey">
                <div class="column xl-12">
                    <h3>Our Culinary Excellence</h3>
                    <p>
                        Eaque temporibus culpa perferendis expedita assumenda mollitia magnam. Lorem ipsum dolor sit
                        amet consectetur adipisicing elit
                        facilis voluptates voluptatum animi numquam quas ea praesentium quaerat maxime sunt odit
                        inventore itaque est et autem sequi nulla.
                        Distinctio obcaecati nesciunt asperiores dolorum tenetur voluptates, nemo alias doloremque.
                        Quos cumque ipsum laudantium odio vero
                        aut odit nostrum aliquam? Nostrum in facilis, minus fuga quasi voluptas explicabo possimus
                        incidunt, expedita tempora eius rem nobis sequi.
                        Doloribus esse sint suscipit quam nisi blanditiis voluptate explicabo.
                    </p>
                </div>
            </div>

            <div class="row width-narrow content-block">
                <div class="column xl-12">

                    <h3>Our Culinary Philosophy</h3>

                    <div class="grid-list-items list-items">
                        <div class="grid-list-items__item list-items__item u-remove-bottom">
                            <div class="list-items__item-header">
                                <h6 class="list-items__item-small-title">Quality</h6>
                            </div>
                            <p>
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Mollitia accusamus
                                consectetur adipisicing elit excepturi corrupti
                                nam quae exercitationem cupiditate, provident ipsa delectus vero possimus
                                reprehenderit quas ut officiis tempora voluptatum
                                .
                            </p>
                        </div>
                        <div class="grid-list-items__item list-items__item u-remove-bottom">
                            <div class="list-items__item-header">
                                <h6 class="list-items__item-small-title">Heritage</h6>
                            </div>
                            <p>
                                Molestias, autem impedit culpa dolores excepturi, quidem unde ducimus, rerum commodi
                                deserunt earum, minus voluptatum.
                                Lorem ipsum dolor sit amet consectetur, adipisicing elit. Saepe doloremque provident
                                quas.
                            </p>
                        </div>
                        <div class="grid-list-items__item list-items__item u-remove-bottom">
                            <div class="list-items__item-header">
                                <h6 class="list-items__item-small-title">Precision</h6>
                            </div>
                            <p>
                                Mollitia accusamus consectetur. Lorem ipsum dolor sit amet consectetur adipisicing
                                elit. Adipisicing elit excepturi corrupti
                                nam quae exercitationem cupiditate, provident ipsa delectus vero possimus
                                reprehenderit quas ut officiis tempora voluptatum.

                            </p>
                        </div>
                        <div class="grid-list-items__item list-items__item u-remove-bottom">
                            <div class="list-items__item-header">
                                <h6 class="list-items__item-small-title">Harmony</h6>
                            </div>
                            <p>
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Mollitia accusamus
                                consectetur adipisicing elit excepturi corrupti
                                nam quae exercitationem cupiditate, provident ipsa delectus vero possimus
                                reprehenderit quas ut officiis tempora voluptatum.
                            </p>
                        </div>
                        <div class="grid-list-items__item list-items__item u-remove-bottom">
                            <div class="list-items__item-header">
                                <h6 class="list-items__item-small-title">Inclusivity</h6>
                            </div>
                            <p>
                                Molestias, autem impedit culpa dolores excepturi, quidem unde ducimus, rerum commodi
                                deserunt earum, minus voluptatum.
                                Lorem ipsum dolor sit amet consectetur, adipisicing elit. Saepe doloremque provident
                                quas.
                            </p>
                        </div>
                        <div class="grid-list-items__item list-items__item u-remove-bottom">
                            <div class="list-items__item-header">
                                <h6 class="list-items__item-small-title">Innovation</h6>
                            </div>
                            <p>
                                Mollitia accusamus consectetur. Lorem ipsum dolor sit amet consectetur adipisicing
                                elit. Adipisicing elit excepturi corrupti
                                nam quae exercitationem cupiditate, provident ipsa delectus vero possimus
                                reprehenderit quas ut officiis tempora voluptatum.
                            </p>
                        </div>
                    </div>

                </div>
            </div>

        </section>

    </article>

    <section id="testimonials" class="s-testimonials">

        <div class="row s-testimonials__content">
            <div class="column xl-12">

                <h3 class="s-testimonials__title text-center">Hear From Our Clients</h3>

                <div class="swiper-container s-testimonials__slider">
                    <div class="swiper-wrapper">

                        <div class="s-testimonials__slide swiper-slide">
                            <div class="s-testimonials__author">
                                <img src="{{ asset('theme/images/avatars/user-02.jpg') }}" class="s-testimonials__avatar">
                                <cite class="s-testimonials__cite">
                                    John Rockefeller
                                    <span>Cleveland, Ohio</span>
                                </cite>
                            </div>
                            <p>
                                Molestiae incidunt consequatur quis ipsa autem nam sit enim magni. Voluptas tempore
                                rem.
                                Explicabo a quaerat sint autem dolore ducimus ut consequatur neque. Nisi dolores
                                quaerat fuga rem nihil nostrum.
                                Laudantium quia consequatur molestias.
                            </p>
                        </div>

                        <div class="s-testimonials__slide swiper-slide">
                            <div class="s-testimonials__author">
                                <img src="{{ asset('theme/images/avatars/user-03.jpg') }}" class="s-testimonials__avatar">
                                <cite class="s-testimonials__cite">
                                    Andrew Carnegie
                                    <span>Pittsburgh, Pennsylvania</span>
                                </cite>
                            </div>
                            <p>
                                Excepturi nam cupiditate culpa doloremque deleniti repellat. Veniam quos repellat
                                voluptas animi adipisci.
                                Nisi eaque consequatur. Voluptatem dignissimos ut ducimus accusantium perspiciatis.
                                Quasi voluptas eius distinctio. Atque eos maxime.
                            </p>
                        </div>

                        <div class="s-testimonials__slide swiper-slide">
                            <div class="s-testimonials__author">
                                <img src="{{ asset('theme/images/avatars/user-01.jpg') }}" class="s-testimonials__avatar">
                                <cite class="s-testimonials__cite">
                                    John Morgan
                                    <span>New York City</span>
                                </cite>
                            </div>
                            <p>
                                Repellat dignissimos libero. Qui sed at corrupti expedita voluptas odit. Nihil ea
                                quia nesciunt. Ducimus aut sed ipsam.
                                Autem eaque officia cum exercitationem sunt voluptatum accusamus. Quasi voluptas
                                eius distinctio.
                                Voluptatem dignissimos ut.
                            </p>
                        </div>

                        <div class="s-testimonials__slide swiper-slide">
                            <div class="s-testimonials__author">
                                <img src="{{ asset('theme/images/avatars/user-06.jpg') }}" class="s-testimonials__avatar">
                                <cite class="s-testimonials__cite">
                                    Henry Ford
                                    <span>Dearborn, Michigan</span>
                                </cite>
                            </div>
                            <p>
                                Nunc interdum lacus sit amet orci. Vestibulum dapibus nunc ac augue. Fusce vel dui.
                                In ac felis
                                quis tortor malesuada pretium. Curabitur vestibulum aliquam leo. Qui sed at corrupti
                                expedita voluptas odit.
                                Nihil ea quia nesciunt. Ducimus aut sed ipsam.
                            </p>
                        </div>

                    </div>

                    <div class="swiper-pagination"></div>
                </div>

            </div>
        </div>
    </section>
@endsection
