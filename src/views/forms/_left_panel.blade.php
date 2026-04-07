<style>
    .process-card {
        text-align: center;
    }

    .box-img {
        width: 162px;
        height: 181px;
        margin: 0 auto;
        padding: 8px;
        position: relative;
        z-index: 2;
        background-color: #fff;
        padding-top: 0px;

        /* Outer hexagon for the box with a border effect */
        clip-path: polygon(50% 3%,
                /* top */
                97% 25%,
                /* top right */
                97% 75%,
                /* bottom right */
                50% 97%,
                /* bottom */
                3% 75%,
                /* bottom left */
                3% 25%
                /* top left */
            );
        overflow: hidden;
        /* ensure inner content is clipped too */
    }

    .box-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: relative;
        z-index: 1;
        clip-path: polygon(50% 7%,
                /* top - increased from 2% to 8% */
                98% 28%,
                /* top right - inset from 97% to 92% & from 25% to 30% */
                98% 77%,
                /* bottom right */
                51% 97%,
                /* bottom */
                2% 77%,
                /* bottom left */
                2% 28%
                /* top left */
            );
    }
</style>
<div class="container">
    <div class="title-area text-center">
        {{-- <span class="sub-title">How We Work</span> --}}
        {{-- <h5 class="sec-title">How We Works</h5> --}}
        <h3>Free Estimate</h3>
        <h5 class="text-warning">How It Works</h5>
    </div>
    <div class="process-card-wrap">

        <div class="process-card">
            <div class="box-img">
                <img src="/post-image/process-card-1-1751458280-HI9g.jpg" alt="icon">
            </div>
            <h5 class="box-title">Quote</h5>
            <p class="box-text" style="font-weight: 400; font-size:14px">
                We will send you a quote shortly.
            </p>
        </div>
        <div class="process-card">
            <div class="box-img">
                <img src="/post-image/process-card-2-1751458231-TFT5.jpg" alt="icon">
            </div>
            <h5 class="box-title">Appointment</h5>
            <p class="box-text" style="font-weight: 400; font-size:14px">
                Schedule an appointment or contact us for any revisions.
            </p>
        </div>
        <div class="process-card">
            <div class="box-img">
                <img src="/post-image/process-card-3-1751458256-ve7z.jpg" alt="icon">
            </div>
            <h5 class="box-title">Service</h5>
            <p class="box-text" style="font-weight: 400; font-size:14px">
                Carry out the service and provide you with a detailed report afterward.
            </p>
        </div>

    </div>
</div>
