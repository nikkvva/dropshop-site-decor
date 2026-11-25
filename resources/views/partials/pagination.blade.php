{{-- Страницы каталога в разметке Bazaro: $products->links('partials.pagination') --}}
@if ($paginator->hasPages())
   <div class="aq-pagination text-center">
      <nav aria-label="Сторінки">
         <ul>
            @if (! $paginator->onFirstPage())
               <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Попередня">&larr;</a></li>
            @endif
            @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
               @if ($page == $paginator->currentPage())
                  <li><span class="current">{{ $page }}</span></li>
               @else
                  <li><a href="{{ $url }}">{{ $page }}</a></li>
               @endif
            @endforeach
            @if ($paginator->hasMorePages())
               <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Наступна">&rarr;</a></li>
            @endif
         </ul>
      </nav>
   </div>
@endif
