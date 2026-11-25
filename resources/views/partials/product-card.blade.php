{{-- Карточка товара в сетке: главная, каталог, «Схожі товари». --}}
<div class="aq-product-item aq-product-main d-flex flex-column justify-content-between mb-15">
   <div class="aq-product-thumb p-relative mb-10">
      <a href="{{ route('product', $product) }}">
         <img class="aq-product-img" src="{{ $product->image() }}" alt="{{ $product->name }}" loading="lazy">
      </a>
   </div>
   <div class="aq-product-content text-center">
      <h4 class="aq-product-title mb-10"><a href="{{ route('product', $product) }}">{{ $product->name }}</a></h4>
      <div class="aq-product-price">
         <ins><span class="aq-product-new-price">{{ $product::money($product->price) }}</span></ins>
      </div>
      @if ($product->is_available)
         <form method="POST" action="{{ route('cart.add', $product) }}" class="site-card-form">
            @csrf
            <button type="submit" class="site-card-buy">До кошика</button>
         </form>
      @endif
   </div>
</div>
