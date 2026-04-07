<!-- =======================
 Instant Roof Quote (Mapbox + Geocoder + Draw + Turf)
 Full updated code (drop-in)
======================= -->

<!-- Mapbox GL -->
<link href="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css" rel="stylesheet" />
<link href="https://unpkg.com/@mapbox/mapbox-gl-geocoder@5.0.1/dist/mapbox-gl-geocoder.css" rel="stylesheet" />

<!-- Mapbox Draw -->
<link href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-draw/v1.5.0/mapbox-gl-draw.css" rel="stylesheet" />

<style>
    /* ===== Prefixed, framework-independent ===== */
    .stq-wrap {
        max-width: 980px;
        margin: 24px auto;
        padding: 0 16px;
        font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        color: #111
    }

    .stq-head {
        margin-bottom: 14px
    }

    .stq-title {
        font-size: 26px;
        font-weight: 750;
        letter-spacing: -0.02em
    }

    .stq-sub {
        margin-top: 6px;
        color: #555;
        font-size: 14px
    }

    .stq-steps {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin: 14px 0 18px
    }

    .stq-stepbadge {
        font-size: 13px;
        padding: 10px 12px;
        border-radius: 12px;
        background: #f7f7f7;
        color: #4b5563;
        border: 1px solid #e5e7eb;
        text-align: center;
        font-weight: 650;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04)
    }

    .stq-stepbadge--active {
        background: #111827;
        color: #fff;
        border-color: #111827;
        box-shadow: 0 8px 18px rgba(17, 24, 39, 0.18)
    }

    @media (max-width:720px) {
        .stq-steps {
            grid-template-columns: repeat(2, minmax(0, 1fr))
        }
    }

    .stq-card {
        border: 1px solid #e6e6e6;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
        margin-bottom: 14px
    }

    .stq-card__hd {
        padding: 14px 14px 0
    }

    .stq-card__title {
        font-weight: 700
    }

    .stq-card__desc {
        margin-top: 6px;
        color: #555;
        font-size: 13px
    }

    .stq-card__bd {
        padding: 14px
    }

    .stq-geocoder {
        margin-bottom: 10px
    }

    .stq-search {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-bottom: 10px
    }

    .stq-search input {
        flex: 1;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #d9d9d9;
        font-size: 14px
    }

    .stq-search button {
        padding: 10px 14px;
        border-radius: 10px;
        border: 1px solid #111;
        background: #111;
        color: #fff;
        font-weight: 650;
        cursor: pointer
    }

    .stq-geocoder .mapboxgl-ctrl-geocoder input {
        display: none
    }

    .stq-geocoder .mapboxgl-ctrl-geocoder .suggestions {
        box-shadow: 0 8px 18px rgba(0, 0, 0, .12)
    }

    .stq-map {
        height: 480px;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e8e8e8
    }

    .stq-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-top: 12px
    }

    .stq-actions--center {
        justify-content: center
    }

    .stq-planlist {
        display: grid;
        gap: 12px
    }

    .stq-plancard {
        border: 1px solid #e6e6e6;
        border-radius: 12px;
        padding: 12px;
        background: #fafafa;
        display: grid;
        gap: 6px
    }

    .stq-plancard--active {
        border-color: #111;
        box-shadow: 0 8px 18px rgba(17, 24, 39, 0.15);
        background: #fff
    }

    .stq-plancard__title {
        font-weight: 750;
        font-size: 15px
    }

    .stq-plancard__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px
    }

    .stq-plancard__price {
        font-size: 18px;
        font-weight: 800
    }

    .stq-plancard__desc {
        font-size: 13px;
        color: #555
    }

    .stq-plancard__meta {
        font-size: 13px;
        color: #555
    }

    .stq-btn {
        border: 1px solid #ddd;
        background: #fff;
        color: #111;
        padding: 10px 12px;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 650
    }

    .stq-btn:hover {
        border-color: #cfcfcf
    }

    .stq-btn:disabled {
        opacity: .55;
        cursor: not-allowed
    }

    .stq-btn--primary {
        background: #111;
        color: #fff;
        border-color: #111
    }

    .stq-btn--ghost {
        padding: 8px 10px;
        font-size: 12.5px
    }

    .stq-btn--send {
        background: #111;
        color: #fff;
        border-color: #111
    }

    .stq-btn--send:hover {
        border-color: #111
    }

    .stq-btn--toggle {
        border-style: dashed;
        color: #333
    }

    .stq-btn--loading {
        position: relative;
        padding-right: 34px
    }

    .stq-btn--loading::after {
        content: "";
        position: absolute;
        top: 50%;
        right: 10px;
        width: 14px;
        height: 14px;
        margin-top: -7px;
        border: 2px solid currentColor;
        border-top-color: transparent;
        border-radius: 50%;
        animation: stq-spin 0.8s linear infinite
    }

    @keyframes stq-spin {
        to {
            transform: rotate(360deg)
        }
    }

    .stq-btn--success {
        background: #118a3a;
        color: #fff;
        border-color: #118a3a
    }

    .stq-hint {
        margin-top: 10px;
        color: #666;
        font-size: 12.5px
    }

    .stq-note {
        padding: 10px 12px;
        border: 1px solid #d6e7ff;
        background: #f3f8ff;
        border-radius: 12px;
        color: #113b73;
        margin: 10px 0
    }

    .stq-note__sub {
        margin-top: 6px;
        color: #2a4f85;
        font-size: 12.5px
    }

    .stq-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px
    }

    @media (max-width:720px) {
        .stq-fields {
            grid-template-columns: 1fr
        }
    }

    .stq-field--full {
        grid-column: 1/-1
    }

    .stq-label {
        display: block;
        font-size: 13px;
        color: #444;
        margin-bottom: 6px;
        font-weight: 650
    }

    .stq-input {
        width: 100%;
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 14px;
        outline: none
    }

    .stq-input:focus {
        border-color: #999
    }

    .stq-hide {
        display: none !important
    }

    .stq-map .mapboxgl-ctrl-group .mapbox-gl-draw_polygon,
    .stq-map .mapboxgl-ctrl-group .mapbox-gl-draw_trash {
        display: none !important
    }

    .stq-map .mapboxgl-ctrl-group {
        background: transparent;
        box-shadow: none;
        border: 0
    }

    /* Small area pill */
    .stq-area {
        margin-top: 10px;
        font-size: 13px;
        color: #333;
    }

    .stq-area strong {
        font-weight: 800
    }

    .stq-area__title {
        font-size: 13px;
        color: #444;
        font-weight: 700;
        margin-bottom: 8px
    }

    .stq-area .stq-area__pill {
        border: 1px solid #e6e6e6;
        padding: 6px 10px;
        border-radius: 999px;
        background: #fafafa;
    }

    .stq-roof-type {
        margin-top: 16px
    }

    .stq-roof-type__title {
        font-size: 13px;
        color: #444;
        font-weight: 700;
        margin-bottom: 8px
    }

    .stq-roof-typegrid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px
    }

    @media (max-width:720px) {
        .stq-roof-typegrid {
            grid-template-columns: repeat(2, minmax(0, 1fr))
        }
    }

    .stq-roofcard {
        border: 1px solid #e6e6e6;
        border-radius: 12px;
        padding: 10px;
        background: #fafafa;
        display: grid;
        gap: 6px;
        align-items: center;
        cursor: pointer
    }

    .stq-roofcard:hover {
        border-color: #cfcfcf
    }

    .stq-roofcard--active {
        border-color: #111;
        box-shadow: 0 8px 18px rgba(17, 24, 39, 0.12);
        background: #fff
    }

    .stq-roofcard__icon {
        width: 38px;
        height: 26px;
        display: inline-block
    }

    .stq-roofcard__label {
        font-size: 13px;
        font-weight: 700;
        color: #111
    }

    .mapboxgl-ctrl button .mapboxgl-ctrl-icon {
        background: #fff;
    }
</style>

<div class="stq-wrap" id="stq-wrap">
    <div class="stq-head">
        <div class="stq-title">Instant Roof Quote</div>
        <div class="stq-sub">Select address & roof → pick pricing → confirm</div>
    </div>

    <div class="stq-steps">
        <div class="stq-stepbadge stq-stepbadge--active" data-stq-badge="1">1. Address & Roof</div>
        <div class="stq-stepbadge" data-stq-badge="2">2. Contact</div>
        <div class="stq-stepbadge" data-stq-badge="3">3. Pricing</div>
    </div>

    <form id="stq-form" class="stq-form" method="POST">
        <!-- If you are in Laravel Blade keep @csrf, otherwise remove -->
        <!-- @csrf -->

        <!-- Hidden payload -->
        <input type="hidden" name="address" id="stq-address" value="">
        <input type="hidden" name="address_add1" id="stq-address-add1" value="">
        <input type="hidden" name="address_city" id="stq-address-city" value="">
        <input type="hidden" name="address_state" id="stq-address-state" value="">
        <input type="hidden" name="address_zip" id="stq-address-zip" value="">
        <input type="hidden" name="address_country" id="stq-address-country" value="">
        <input type="hidden" name="place_id" id="stq-place-id" value="">
        <input type="hidden" name="lng" id="stq-lng" value="">
        <input type="hidden" name="lat" id="stq-lat" value="">
        <input type="hidden" name="roof_mode" id="stq-roof-mode" value="custom">
        <input type="hidden" name="roof_polygon" id="stq-roof-polygon" value="">
        <input type="hidden" name="roof_area_m2" id="stq-roof-area-m2" value="">
        <input type="hidden" name="roof_slope" id="stq-roof-slope" value="">
        <input type="hidden" name="pricing_plan" id="stq-plan" value="">

        <!-- STEP 1 -->
        <section class="stq-card" data-stq-step="1">
            <div class="stq-card__hd">
                <div class="stq-card__title">What Will My Roof Cost?</div>
                <div class="stq-card__desc">Start typing your address and pick a result.</div>
            </div>

            <div class="stq-card__bd">
                <div class="stq-search">
                    <input type="text" id="stq-address-input" placeholder="Search your address">
                    <button type="button" id="stq-address-search">Search</button>
                </div>
                <div class="stq-hint">Tip: The map zooms after you select an address.</div>

                <div style="min-height:400px; margin-top:20px;">
                    <div id="stq-geocoder" class="stq-geocoder"></div>
                    <div id="stq-map" class="stq-map"></div>
                </div>

                <div>

                    <div class="stq-area stq-hide" id="stq-area-wrap">
                        <div class="stq-area__title">Estimated Roof Area</div>
                        <div>
                            <span class="stq-area__pill">Selected area: <strong id="stq-area-m2">—</strong> m²</span>
                            <span class="stq-area__pill">Selected area: <strong id="stq-area-ft2">—</strong> ft²</span>
                        </div>
                    </div>

                    <div class="stq-roof-type stq-hide" id="stq-roof-type-wrap">
                        <div class="stq-roof-type__title">Roof slope type</div>
                        <div class="stq-roof-typegrid" id="stq-roof-type-grid">

                            @if (isset($packageCategories))
                                @foreach ($packageCategories as $categoryItem)
                                    <button type="button" class="stq-roofcard"
                                        data-roof-slope="{{ $categoryItem->id }}">
                                        @if ($categoryItem->icon_svg)
                                            {!! $categoryItem->icon_svg !!}
                                        @endif
                                        <span class="stq-roofcard__label">{{ $categoryItem->value }}</span>
                                    </button>
                                @endforeach
                            @endif

                        </div>
                    </div>
                </div>

                <div class="stq-actions stq-actions--center stq-hide" id="stq-next-1-wrap" style="margin-top: 60px;">
                    <button type="button" class="stq-btn stq-btn--primary" id="stq-next-1"
                        disabled>Continue</button>
                </div>

            </div>
        </section>

        <!-- STEP 2 -->
        <section class="stq-card stq-hide" data-stq-step="2">
            <div class="stq-card__hd">
                <div class="stq-card__title">Step 2 — Contact details</div>
                <div class="stq-card__desc">Tell us where to send your price.</div>
            </div>

            <div class="stq-card__bd">
                <div class="stq-fields" style="margin-bottom:16px;">
                    <div class="stq-field">
                        <label class="stq-label">First name</label>
                        <input class="stq-input" name="first_name" required>
                    </div>

                    <div class="stq-field">
                        <label class="stq-label">Last name</label>
                        <input class="stq-input" name="last_name" required>
                    </div>

                    <div class="stq-field">
                        <label class="stq-label">Phone</label>
                        <input class="stq-input" name="phone" required>
                    </div>

                    <div class="stq-field">
                        <label class="stq-label">Email</label>
                        <input class="stq-input" type="email" name="email" required>
                    </div>
                </div>

                <div class="stq-actions">
                    <button type="button" class="stq-btn" id="stq-back-3">Back</button>
                    <button type="button" class="stq-btn stq-btn--primary" id="stq-next-3" disabled>Show My
                        Price</button>
                </div>
            </div>
        </section>

        <!-- STEP 3 -->
        <section class="stq-card stq-hide" data-stq-step="3">
            <div class="stq-card__hd">
                <div class="stq-card__title">Step 3 — Pricing list</div>
                <div class="stq-card__desc">Select a package to submit.</div>
            </div>

            <div class="stq-card__bd" id="plan_list">
                {{--  --}}
            </div>
        </section>
    </form>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Mapbox JS -->
<script src="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js"></script>
<script src="https://unpkg.com/@mapbox/mapbox-gl-geocoder@5.0.1/dist/mapbox-gl-geocoder.min.js"></script>

<!-- Mapbox Draw -->
<script src="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-draw/v1.5.0/mapbox-gl-draw.js"></script>

<!-- Turf -->
<script src="https://unpkg.com/@turf/turf@6.5.0/turf.min.js"></script>

<script>
    (function($) {

        // Global AJAX setup for CSRF (only once)
        if (typeof $._csrfInitialized === "undefined") {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $._csrfInitialized = true;
        }


        function sendAjax({
                url = '',
                method = 'POST',
                data = {},
                dataType = 'json'
            },
            onSuccess = () => {},
            onError = () => {},
            onFinally = () => {}
        ) {
            $.ajax({
                url: url,
                type: method,
                data: data,
                dataType: dataType,
                success: onSuccess,
                error: onError,
                complete: onFinally
            });
        }


        // ✅ your token (hardcoded)
        const mapboxToken =
            "pk.eyJ1IjoibGVraHJhanN5c3RoYSIsImEiOiJjbWpoYXRib3kxNTJoM2RxeHZxZ2VpOGZ6In0.Tsfe9GHjd5pBn5dfkVAVMw";

        const hasMapbox = !!mapboxToken &&
            window.mapboxgl &&
            window.MapboxGeocoder;
        const hasDraw = !!window.MapboxDraw;

        // ---- shared map state ----
        let map = null;
        let marker = null;
        let geocoder = null;
        let draw = null;

        function debounce(fn, wait) {
            let timer = null;
            return function(...args) {
                window.clearTimeout(timer);
                timer = window.setTimeout(() => fn.apply(this, args), wait);
            };
        }

        function flyToAddress(lng, lat, zoom) {
            if (!map) return;
            const canvas = map.getCanvas();
            const offsetY = canvas ? Math.round(-(canvas.clientHeight * 0.25)) : -120;
            map.flyTo({
                center: [lng, lat],
                zoom: zoom || map.getZoom(),
                offset: [0, offsetY]
            });
        }

        function setAddressParts(parts) {
            $("#stq-address-add1").val(parts.add1 || "");
            $("#stq-address-city").val(parts.city || "");
            $("#stq-address-state").val(parts.state || "");
            $("#stq-address-zip").val(parts.zip || "");
            $("#stq-address-country").val(parts.country || "");
        }

        function extractAddressParts(feature) {
            if (!feature) {
                return {
                    add1: "",
                    city: "",
                    state: "",
                    zip: "",
                    country: ""
                };
            }

            const add1 = (feature.address ? feature.address + " " : "") + (feature.text || "");
            const context = Array.isArray(feature.context) ? feature.context : [];
            const getByType = function(type) {
                const item = context.find((c) => c.id && c.id.indexOf(type + ".") === 0);
                return item ? item.text : "";
            };

            const city = getByType("place") || getByType("locality") || getByType("district") || getByType(
                "neighborhood");
            const state = getByType("region");
            const zip = getByType("postcode");
            const country = getByType("country");

            return {
                add1: add1,
                city: city,
                state: state,
                zip: zip,
                country: country
            };
        }

        function reverseGeocode(lng, lat) {
            if (!mapboxToken || !window.fetch) return;
            const url =
                "https://api.mapbox.com/geocoding/v5/mapbox.places/" +
                encodeURIComponent(lng + "," + lat) +
                ".json?access_token=" +
                encodeURIComponent(mapboxToken) +
                "&types=address&limit=1";

            fetch(url)
                .then((res) => (res.ok ? res.json() : null))
                .then((data) => {
                    const feature = data && data.features && data.features[0];
                    if (!feature) return;
                    const placeName = feature.place_name || "";
                    $addressInput.val(placeName);
                    $("#stq-address").val(placeName);
                    $("#stq-place-id").val(feature.id || "");
                    setAddressParts(extractAddressParts(feature));
                })
                .catch(() => {});
        }

        // ----- State helpers -----
        function stqSetBadge(step) {
            $(".stq-stepbadge").removeClass("stq-stepbadge--active");
            $('.stq-stepbadge[data-stq-badge="' + step + '"]').addClass("stq-stepbadge--active");
        }

        function stqShowStep(step) {
            $("[data-stq-step]").addClass("stq-hide");
            $('[data-stq-step="' + step + '"]').removeClass("stq-hide");
            stqSetBadge(step);
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        }

        function updateAreaUI() {
            const mode = $("#stq-roof-mode").val();
            $("#stq-area-mode").text(mode === "custom" ? "Custom" : "Full");

            const hasAddress = $.trim($("#stq-address").val()).length > 0;
            const areaM2 = parseFloat($("#stq-roof-area-m2").val());
            if (!areaM2 || Number.isNaN(areaM2) || areaM2 <= 0) {
                $("#stq-area-m2").text("—");
                $("#stq-area-ft2").text("—");
                $("#stq-area-wrap").addClass("stq-hide");
                $("#stq-roof-type-wrap").addClass("stq-hide");
                $("#stq-roof-slope").val("");
                $("#stq-roof-type-grid .stq-roofcard").removeClass("stq-roofcard--active");
                $("#stq-next-1-wrap").addClass("stq-hide");
                $("#stq-next-1").prop("disabled", true);
                return;
            }
            const ft2 = areaM2 * 10.763910416709722;
            $("#stq-area-m2").text(areaM2.toFixed(2));
            $("#stq-area-ft2").text(ft2.toFixed(2));
            $("#stq-area-wrap").removeClass("stq-hide");
            $("#stq-roof-type-wrap").removeClass("stq-hide");
            if (hasAddress) {
                $("#stq-next-1-wrap").removeClass("stq-hide");
                $("#stq-next-1").prop("disabled", false);
            } else {
                $("#stq-next-1-wrap").addClass("stq-hide");
                $("#stq-next-1").prop("disabled", true);
            }
        }

        function clearRoofSelection() {
            $("#stq-roof-polygon").val("");
            $("#stq-roof-area-m2").val("");
            updateAreaUI();

            if (draw) {
                try {
                    const all = draw.getAll();
                    if (all && all.features && all.features.length) {
                        all.features.forEach(f => draw.delete(f.id));
                    }
                } catch (e) {}
            }
        }

        let isSimplifyingRoof = false;

        function getLargestPolygonRing(feature) {
            if (!feature || !feature.geometry) return null;
            if (feature.geometry.type === "Polygon") {
                return feature.geometry.coordinates[0] || null;
            }
            if (feature.geometry.type === "MultiPolygon" && window.turf) {
                let best = null;
                let bestArea = -Infinity;
                feature.geometry.coordinates.forEach((poly) => {
                    try {
                        const candidate = turf.polygon(poly);
                        const area = turf.area(candidate);
                        if (area > bestArea) {
                            bestArea = area;
                            best = poly[0] || null;
                        }
                    } catch (e) {}
                });
                return best;
            }
            return null;
        }

        function simplifyToCornerPolygon(feature) {
            if (!feature || !feature.geometry || !window.turf) return null;
            const ring = getLargestPolygonRing(feature);
            if (ring && ring.length === 5) {
                return feature;
            }
            try {
                const bounds = turf.bbox(feature);
                const box = turf.bboxPolygon(bounds);
                return box && box.geometry ? box : null;
            } catch (e) {
                return null;
            }
        }

        function saveRoofSelectionFromDraw() {
            if (!draw || isSimplifyingRoof) return;

            const fc = draw.getAll();
            if (!fc || !fc.features || fc.features.length === 0) {
                $("#stq-roof-polygon").val("");
                $("#stq-roof-area-m2").val("");
                updateAreaUI();
                return;
            }

            const polys = fc.features.filter(f => f.geometry && f.geometry.type === "Polygon");
            if (!polys.length) {
                $("#stq-roof-polygon").val("");
                $("#stq-roof-area-m2").val("");
                updateAreaUI();
                return;
            }

            // Keep only the last polygon for clean UX
            const last = polys[polys.length - 1];

            $("#stq-roof-polygon").val(JSON.stringify(last));

            if (window.turf) {
                const areaM2 = turf.area(last);
                $("#stq-roof-area-m2").val(String(areaM2 || ""));
            }

            // If user drew multiple polygons, remove older ones
            if (polys.length > 1) {
                polys.slice(0, -1).forEach(p => draw.delete(p.id));
            }

            updateAreaUI();
        }

        function enableCustomDrawing() {
            if (!map || !draw) return;

            try {
                draw.changeMode("draw_polygon");
            } catch (e) {}

            // Zoom closer if address already selected
            const lng = parseFloat($("#stq-lng").val());
            const lat = parseFloat($("#stq-lat").val());
            if (!Number.isNaN(lng) && !Number.isNaN(lat)) {
                flyToAddress(lng, lat, Math.max(map.getZoom(), 19));
            }
        }

        function buildRotatedBox(lngLat, widthM, heightM, angleDeg) {
            if (!window.turf) return null;
            const center = turf.point(lngLat);
            const halfW = widthM / 2;
            const halfH = heightM / 2;
            const axis = angleDeg;
            const perp = angleDeg + 90;
            try {
                const east = turf.destination(center, halfW, axis, {
                    units: "meters"
                });
                const west = turf.destination(center, halfW, axis + 180, {
                    units: "meters"
                });
                const c1 = turf.destination(east, halfH, perp, {
                    units: "meters"
                }).geometry.coordinates;
                const c2 = turf.destination(east, halfH, perp + 180, {
                    units: "meters"
                }).geometry.coordinates;
                const c3 = turf.destination(west, halfH, perp + 180, {
                    units: "meters"
                }).geometry.coordinates;
                const c4 = turf.destination(west, halfH, perp, {
                    units: "meters"
                }).geometry.coordinates;
                return turf.polygon([
                    [c1, c2, c3, c4, c1]
                ]);
            } catch (e) {
                return null;
            }
        }

        function seedRoofPolygonAt(lngLat) {
            if (!map || !draw) return;
            let geometry = null;
            const layers = ["building", "building-outline", "building-extrusion"].filter((layer) => map.getLayer(
                layer));
            const point = map.project(lngLat);
            const searchRadiusPx = 40;
            const bbox = [
                [point.x - searchRadiusPx, point.y - searchRadiusPx],
                [point.x + searchRadiusPx, point.y + searchRadiusPx]
            ];
            const features = map.queryRenderedFeatures(bbox, {
                layers: layers.length ? layers : undefined
            });
            const roofCandidates = features.filter((feature) => {
                return ["Polygon", "MultiPolygon"].includes(feature.geometry.type);
            });
            let roofFeature = roofCandidates[0] || null;
            if (roofCandidates.length > 1 && window.turf) {
                let best = null;
                let bestArea = -Infinity;
                roofCandidates.forEach((feature) => {
                    try {
                        const area = turf.area(feature);
                        if (area > bestArea) {
                            bestArea = area;
                            best = feature;
                        }
                    } catch (e) {}
                });
                roofFeature = best || roofFeature;
            }

            if (roofFeature && window.turf) {
                try {
                    const bounds = turf.bbox(roofFeature);
                    const box = turf.bboxPolygon(bounds);
                    geometry = box && box.geometry ? box : null;
                } catch (e) {}
            }

            if (!geometry) {
                const seedBoxSizeM = 16;
                geometry = buildRotatedBox(lngLat, seedBoxSizeM, seedBoxSizeM, 0);
            }

            if (!geometry || !geometry.geometry) return;

            draw.deleteAll();
            const ids = draw.add({
                type: "Feature",
                geometry: geometry.geometry,
                properties: {}
            });
            saveRoofSelectionFromDraw();

            const featureId = Array.isArray(ids) ? ids[0] : ids;
            if (featureId) {
                try {
                    draw.changeMode("direct_select", {
                        featureId: featureId
                    });
                } catch (e) {}
            }
        }

        // ----- Map init -----
        const $addressInput = $("#stq-address-input");
        const $addressSearch = $("#stq-address-search");

        function initMap() {
            if (map || !hasMapbox) return;
            const mapEl = document.getElementById("stq-map");
            if (!mapEl) return;
            if (mapEl.dataset.stqMapInitialized === "true") return;
            mapEl.dataset.stqMapInitialized = "true";

            mapboxgl.accessToken = mapboxToken;
            if (mapboxgl.setTelemetryEnabled) {
                mapboxgl.setTelemetryEnabled(false);
            }

            map = new mapboxgl.Map({
                container: "stq-map",
                style: "mapbox://styles/mapbox/satellite-streets-v12",
                center: [-97.0, 32.8],
                zoom: 9
            });

            map.addControl(new mapboxgl.NavigationControl(), "bottom-left");

            marker = new mapboxgl.Marker({
                draggable: true
            });
            marker.on("dragend", function() {
                const pos = marker.getLngLat();
                $("#stq-lng").val(pos.lng);
                $("#stq-lat").val(pos.lat);
                reverseGeocode(pos.lng, pos.lat);
                clearRoofSelection();
                $("#stq-plan").val("");
                $("#stq-submit").prop("disabled", true);
                if (hasDraw) {
                    seedRoofPolygonAt([pos.lng, pos.lat]);
                }
            });

            geocoder = new MapboxGeocoder({
                accessToken: mapboxgl.accessToken,
                mapboxgl: mapboxgl,
                marker: false,
                placeholder: "Search your address…",
                types: "address"
            });

            $("#stq-geocoder").append(geocoder.onAdd(map));

            if (hasDraw) {
                draw = new MapboxDraw({
                    displayControlsDefault: false,
                    controls: {
                        polygon: true,
                        trash: true
                    },
                    defaultMode: "simple_select"
                });

                map.addControl(draw, "bottom-left");

                map.on("draw.create", saveRoofSelectionFromDraw);
                map.on("draw.update", saveRoofSelectionFromDraw);
                map.on("draw.delete", saveRoofSelectionFromDraw);
            }

            function runAddressSearch() {
                const query = $addressInput.val().trim();
                if (!query) return;
                geocoder.query(query);
            }

            const runAddressSearchDebounced = debounce(runAddressSearch, 250);

            $addressSearch.on("click", runAddressSearch);
            $addressInput.on("input", runAddressSearchDebounced);
            $addressInput.on("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    runAddressSearch();
                }
            });

            geocoder.on("result", function(e) {
                const f = e.result;
                const lng = f.center[0];
                const lat = f.center[1];

                $addressInput.val(f.place_name || "");
                $("#stq-address").val(f.place_name || "");
                $("#stq-place-id").val(f.id || "");
                $("#stq-lng").val(lng);
                $("#stq-lat").val(lat);
                setAddressParts(extractAddressParts(f));

                marker.setLngLat([lng, lat]).addTo(map);
                flyToAddress(lng, lat, 19);

                $("#stq-next-1").prop("disabled", false);

                // Reset downstream selections if address changes
                clearRoofSelection();
                $("#stq-plan").val("");
                $("#stq-submit").prop("disabled", true);

                // If already in custom mode, prompt drawing again
                if ($("#stq-roof-mode").val() === "custom") {
                    enableCustomDrawing();
                }
                map.once("idle", function() {
                    seedRoofPolygonAt([lng, lat]);
                });
            });
        }

        if (!hasMapbox) {
            $("#stq-map").addClass("stq-hide");
            $("#stq-geocoder").addClass("stq-hide");
            $addressInput.prop("placeholder", "Address search is disabled (Mapbox not available).");
            $("#stq-next-1").prop("disabled", false);
        } else {
            $(window).on("load", function() {
                initMap();
                if (map) {
                    setTimeout(() => map.resize(), 50);
                }
            });
        }

        // ----- Step nav -----
        function updateContactNextState() {
            const fields = [
                'input[name="first_name"]',
                'input[name="last_name"]',
                'input[name="phone"]',
                'input[name="email"]'
            ];
            const allFilled = fields.every((selector) => $.trim($(selector).val()).length > 0);
            $("#stq-next-3").prop("disabled", !allFilled);
        }

        $("#stq-next-1").on("click", function() {
            const mode = $("#stq-roof-mode").val();

            if (mode === "custom" && !$("#stq-roof-polygon").val()) {
                alert("Please draw your custom roof section (polygon) before continuing.");
                return;
            }
            stqShowStep(2);
            updateAreaUI();
            updateContactNextState();
        });
        $("#stq-back-3").on("click", function() {
            stqShowStep(1);
            updateAreaUI();
        });
        $(document).off("click", "#stq-back-4").on("click", "#stq-back-4", function(e) {
            e.preventDefault();
            console.log("back");
            stqShowStep(2);
            updateContactNextState();
        });

        $("#stq-next-3").on("click", function() {
            // stqShowStep(3);
            const data = $("#stq-form").serializeArray();
            console.log("submit")
            sendAjax({
                url: '/vendor-package-fetch',
                method: 'POST',
                data
            }, function(resp) {
                console.log({
                    resp
                });
                $("#plan_list").html(resp.template);
                stqShowStep(3);
            }, function(error) {
                console.log({
                    error
                })
            })
        });

        // Default to custom drawing mode
        $("#stq-roof-mode").val("custom");

        // ----- Pricing selection -----
        function applyPlan(plan) {
            $("#stq-plan").val(plan);
            $("#stq-submit").prop("disabled", false);
            const selectedCard = $('.stq-plancard[data-stq-plan="' + plan + '"]');
            $(".stq-plancard").removeClass("stq-plancard--active");
            selectedCard.addClass("stq-plancard--active");
        }

        function serializeFormToObject($form) {
            const payload = {};
            $form.serializeArray().forEach(function(item) {
                if (payload[item.name] !== undefined) {
                    if (!Array.isArray(payload[item.name])) {
                        payload[item.name] = [payload[item.name]];
                    }
                    payload[item.name].push(item.value);
                } else {
                    payload[item.name] = item.value;
                }
            });
            return payload;
        }

        $(document).on("click", ".stq-plancard__toggle", function(e) {
            e.preventDefault();
            e.stopPropagation();
            const $card = $(this).closest(".stq-plancard");
            const $desc = $card.find(".stq-plancard__desc");
            const isHidden = $desc.hasClass("stq-hide");
            if (isHidden) {
                $desc.removeClass("stq-hide");
                $(this).text("Hide Details").attr("aria-expanded", "true");
            } else {
                $desc.addClass("stq-hide");
                $(this).text("See Details").attr("aria-expanded", "false");
            }
        });

        $(document).on("click", ".stq-plancard__send", function(e) {
            e.preventDefault();
            e.stopPropagation();
            const $btn = $(this);
            if ($btn.data("loading")) {
                return;
            }
            const payload = serializeFormToObject($("#stq-form"));
            const addressObj = {
                full: $("#stq-address").val() || "",
                add1: $("#stq-address-add1").val() || "",
                city: $("#stq-address-city").val() || "",
                state: $("#stq-address-state").val() || "",
                zip: $("#stq-address-zip").val() || "",
                country: $("#stq-address-country").val() || "",
                lat: $("#stq-lat").val() || "",
                lng: $("#stq-lng").val() || ""
            };
            const contactObj = {
                fname: $('input[name="first_name"]').val() || "",
                lname: $('input[name="last_name"]').val() || "",
                email: $('input[name="email"]').val() || "",
                phone_no: $('input[name="phone"]').val() || ""
            };
            payload.address = addressObj;
            payload.contact = contactObj;
            payload.package_id = $btn.data("id");
            payload.package_name = $btn.data("name");
            payload.total_price = $btn.data("total-price");
            console.log({
                payload
            });
            const originalLabel = $btn.text();
            $btn.data("loading", true);
            $btn.prop("disabled", true).addClass("stq-btn--loading").text("Sending...");
            sendAjax({
                url: '/vendor-package-send-details',
                method: 'POST',
                data: payload
            }, function(resp) {
                $("#stq-wrap").empty().append(resp.temp);
            }, function(error) {
                console.log({
                    error
                });
                $btn.prop("disabled", false).removeClass("stq-btn--loading").text("Retry");
                $btn.data("loading", false);
            }, function() {
                if ($btn.data("loading")) {
                    $btn.prop("disabled", false).removeClass("stq-btn--loading").text(
                    originalLabel);
                    $btn.data("loading", false);
                }
            });
        });

        $(".stq-plancard").on("click", function() {
            applyPlan($(this).data("stq-plan"));
        });

        $("#stq-roof-type-grid .stq-roofcard").on("click", function() {
            const slope = $(this).data("roof-slope");
            $("#stq-roof-slope").val(slope || "");
            $("#stq-roof-type-grid .stq-roofcard").removeClass("stq-roofcard--active");
            $(this).addClass("stq-roofcard--active");
        });

        $('input[name="first_name"], input[name="last_name"], input[name="phone"], input[name="email"]').on("input",
            function() {
                updateContactNextState();
            });

        // Initial
        stqShowStep(1);
        updateAreaUI();
        $("#stq-submit").prop("disabled", true);
        updateContactNextState();

    })(jQuery);
</script>
