@extends('layouts.theme')

@section('title', 'Menu')

@section('content')
    <article class="s-content">
        <section class="s-pageheader pageheader"
            style="background-image:url('{{ asset('theme/images/pageheader/pageheader-menu-bg-3000.jpg') }}')">
            <div class="row">
                <div class="column xl-12 s-pageheader__content">
                    <h1 class="page-title">
                        Discover Our Menu
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

            <div class="row width-narrower content-block">
                <div class="column xl-12">

                    <div class="menu-block">
                        @if ($products->isEmpty())
                            <div class="menublock-item__thumb">
                                <h3>No products available at the moment.
                                    <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f64a/512.gif" width="48">
                                </h3>
                            </div>
                        @else
                            @foreach ($products as $category => $items)
                                <div class="menu-block__group">
                                    <h2 class="h6 menu-block__cat-name">{{ ucfirst($category) }}</h2>

                                    <ul class="menu-list">
                                        @foreach ($items as $product)
                                            <li class="menu-list__item">
                                                <div class="menu-list__item-desc">
                                                    <h4>{{ $product->name }}</h4>
                                                    <p>{{ $product->description }}</p>
                                                </div>
                                                <div class="menu-list__item-price">
                                                    <span>$</span>{{ number_format($product->price, 2) }}
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <div class="row width-narrower row-x-center content-block content-block--grey cta-block">
                <div class="column xl-10 mob-12">
                    <h3>Feast on a Flavorful and Unique Moment</h3>
                    <p class="attention-getter">
                        Eaque temporibus culpa perferendis expedita assumenda mollitia magnam. Lorem ipsum dolor sit
                        amet consectetur adipisicing elit
                        facilis quaerat maxime perferendis expedita sunt odi.
                    </p>
                    <a href="{{ route('website.reservation') }}" class="btn btn--primary u-fullwidth">Book a Table Now</a>
                </div>
            </div>
        </section>
    </article>
@endsection
