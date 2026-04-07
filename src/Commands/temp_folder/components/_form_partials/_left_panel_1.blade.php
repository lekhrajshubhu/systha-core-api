<div class="container">
    <div id="title-container">
        @if (!empty($page?->content?->page_content))
            {!! $page->content->page_content !!}
        @else
            <h2 class="mb-2">Cleaning Service</h2>
            <h4 class="mb-2 text-warning">Free Estimate Form</h4>
            <p>A simple multi-step form to help you get an accurate and instant estimate for your
                home cleaning needs. Just answer a few quick questions to get started with a
                professional, hassle-free service tailored to your space.</p>
        @endif
    </div>
</div>
