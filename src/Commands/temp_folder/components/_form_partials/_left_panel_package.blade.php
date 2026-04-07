<div class="container">
    <div id="title-container">
        <h2 class="mb-2">{{$package->package_name}}</h2>
        <div class="mb-4">
            @if(isset($package->package_thumb))
            <img src="/package-image/{{ $package->package_thumb }}" style="width: 100%" alt="Image" />
            @endif
        </div>
        <div style="font-size: 0.9rem">
            {!! $package->description !!}
        </div>
    </div>
</div>
