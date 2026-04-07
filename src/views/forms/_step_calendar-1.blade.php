<style>
    /* /* @import url(https://fonts.googleapis.com/css?family=Montserrat:200,400,700); */



    .presentation,
    .presentation fieldset {
        border: 0;
        min-width: 15rem;
    }

    .presentation label {
        line-height: 1;
        padding: 0;
        margin: 0 0 0.5em 0;
        white-space: nowrap;
        float: left;
    }

    .presentation label:not(only-child) {
        width: 60%;
    }

    .presentation label~label {
        margin-top: -1.5em;
        width: 35%;
        float: right;
    }

    .presentation input {
        width: 100%;
        float: left;
        margin: 0;
    }

    .presentation input[type="date"]:not(only-child) {
        width: 60%;
    }

    .presentation input[type="time"]:not(only-child) {
        width: 35%;
        float: right;
    }

    :root {
        --ui-datepicker-bg: #fff;
        --ui-datepicker-shadow: 0 0.3rem 1rem 0 rgba(0, 0, 0, 0.15);
        --ui-datepicker-border-radius: 0.3rem;
        --ui-datepicker-border-color: #e7e9ed;
        --ui-datepicker-today-bg: #109899;
        --ui-datepicker-today-color: #fff;
        --ui-datepicker-selected-bg: #e77;
        --ui-datepicker-selected-color: #fff;
        --ui-datepicker-error-color: red;
        --ui-datepicker-zindex: 1020;
        --ui-datepicker-breakpoint-sm: 360px;
        --ui-datepicker-breakpoint-md: 678px;
        --ui-datepicker-breakpoint-lg: 1098px;
        --ui-datepicker-breakpoint-height: 640px;
    }

    input {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        display: inline-block;
        padding: 0.5em 0.7rem;
        font: inherit;
        line-height: 1.5;
        border: 0.075em solid currentColor;
        border-radius: 0.3rem;
        outline: 0;
        background: white;
        /* height: 2.25em; */
        z-index: 1;
        will-change: color, background, box-shadow;
        transition: color 0.2s ease, box-shadow 0.2s ease,
            background 0.2s ease;
    }

    input::-moz-placeholder {
        opacity: 0.5;
    }

    input:-ms-input-placeholder {
        opacity: 0.5;
    }

    input::placeholder {
        opacity: 0.5;
    }

    input:invalid,
    input:invalid:required {
        outline: 1px !important;
        outline-color: red !important;
    }

    input:focus,
    input.focus {
        color: #109899;
        box-shadow: 0 0 0 0.2rem rgba(16, 152, 153, 0.25);
        z-index: 2;
    }

    input:focus::-moz-placeholder,
    input.focus::-moz-placeholder {
        color: #109899;
        opacity: 1;
    }

    input:focus:-ms-input-placeholder,
    input.focus:-ms-input-placeholder {
        color: #109899;
        opacity: 1;
    }

    input:focus::placeholder,
    input.focus::placeholder {
        color: #109899;
        opacity: 1;
    }

    input::selection,
    input::-webkit-datetime-edit-day-field:focus,
    input::-webkit-datetime-edit-month-field:focus,
    input::-webkit-datetime-edit-year-field:focus,
    input::-webkit-datetime-edit-minute-field:focus,
    input::-webkit-datetime-edit-hour-field:focus {
        color: #fff;
        background: #109899;
    }

    input[type="time"],
    input[type*="date"],
    input[type="week"],
    input[type="month"],
    input[type="year"],
    input[data-datepicker] {
        text-overflow: ellipsis;
        overflow: hidden;
        padding-right: 1.75em;
        background-repeat: no-repeat;
        background-size: 1.25em 1.25em;
        background-position: calc(100% - 0.55em) center;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 9 9'%3e%3cpath d='M1.8.5v.8h-.7c-.2 0-.4.2-.4.4v6.5c0 .2.2.4.4.4H8c.2 0 .4-.2.4-.4V1.6c0-.2-.2-.4-.4-.4h-.8V.5H6v.8H3V.5H1.8zm-.3 3h6.1v4.2H1.5V3.5zm.3.4v1.5h1.5V3.9H1.8zm1.9 0v1.5h1.5V3.9H3.7zm1.9 0v1.5h1.5V3.9H5.6zM1.8 5.8v1.5h1.5V5.8H1.8zm1.9 0v1.5h1.5V5.8H3.7z' fill='%23222'/%3e%3c/svg%3e");
        cursor: default;
        -webkit-user-select: none !important;
        -moz-user-select: none !important;
        -ms-user-select: none !important;
        user-select: none !important;
    }

    input[type="time"]:focus,
    input[type="time"].focus,
    input[type*="date"]:focus,
    input[type*="date"].focus,
    input[type="week"]:focus,
    input[type="week"].focus,
    input[type="month"]:focus,
    input[type="month"].focus,
    input[type="year"]:focus,
    input[type="year"].focus,
    input[data-datepicker]:focus,
    input[data-datepicker].focus {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 9 9'%3e%3cpath d='M1.8.5v.8h-.7c-.2 0-.4.2-.4.4v6.5c0 .2.2.4.4.4H8c.2 0 .4-.2.4-.4V1.6c0-.2-.2-.4-.4-.4h-.8V.5H6v.8H3V.5H1.8zm-.3 3h6.1v4.2H1.5V3.5zm.3.4v1.5h1.5V3.9H1.8zm1.9 0v1.5h1.5V3.9H3.7zm1.9 0v1.5h1.5V3.9H5.6zM1.8 5.8v1.5h1.5V5.8H1.8zm1.9 0v1.5h1.5V5.8H3.7z' fill='%23109899'/%3e%3c/svg%3e");
    }

    input[type="time"]::-webkit-spin-button,
    input[type="time"]::-webkit-outer-spin-button,
    input[type="time"]::-webkit-inner-spin-button,
    input[type*="date"]::-webkit-spin-button,
    input[type*="date"]::-webkit-outer-spin-button,
    input[type*="date"]::-webkit-inner-spin-button,
    input[type="week"]::-webkit-spin-button,
    input[type="week"]::-webkit-outer-spin-button,
    input[type="week"]::-webkit-inner-spin-button,
    input[type="month"]::-webkit-spin-button,
    input[type="month"]::-webkit-outer-spin-button,
    input[type="month"]::-webkit-inner-spin-button,
    input[type="year"]::-webkit-spin-button,
    input[type="year"]::-webkit-outer-spin-button,
    input[type="year"]::-webkit-inner-spin-button,
    input[data-datepicker]::-webkit-spin-button,
    input[data-datepicker]::-webkit-outer-spin-button,
    input[data-datepicker]::-webkit-inner-spin-button {
        display: none;
    }

    input[type="time"]::-webkit-clear-button,
    input[type*="date"]::-webkit-clear-button,
    input[type="week"]::-webkit-clear-button,
    input[type="month"]::-webkit-clear-button,
    input[type="year"]::-webkit-clear-button,
    input[data-datepicker]::-webkit-clear-button {
        display: none;
    }

    input[type="time"]::-ms-clear,
    input[type*="date"]::-ms-clear,
    input[type="week"]::-ms-clear,
    input[type="month"]::-ms-clear,
    input[type="year"]::-ms-clear,
    input[data-datepicker]::-ms-clear {
        display: none;
    }

    input[type="time"]::-webkit-calendar-picker-indicator,
    input[type*="date"]::-webkit-calendar-picker-indicator,
    input[type="week"]::-webkit-calendar-picker-indicator,
    input[type="month"]::-webkit-calendar-picker-indicator,
    input[type="year"]::-webkit-calendar-picker-indicator,
    input[data-datepicker]::-webkit-calendar-picker-indicator {
        right: 1.25em;
        width: 1em;
        opacity: 0;
        pointer-events: none;
        margin: 0;
    }

    input[type="time"] {
        /* autoprefixer: ignore next */
        -moz-appearance: textfield;
    }

    input[type="time"],
    input[data-datepicker-mode="time"] {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 9 9'%3e%3cpath d='M4.5.5c-2.2 0-4 1.8-4 4s1.8 4 4 4 4-1.8 4-4-1.8-4-4-4zm1.8 5.2l-.4.1-.2-.1-1.4-1-.1-.1v-.1-.1-.1-.1L5 1.3c0-.2.2-.3.4-.2.2.1.3.3.3.5L5 4.3l1.2.8c.1.2.2.4.1.6z' fill='%23222'/%3e%3c/svg%3e");
    }

    input[type="time"]:focus,
    input[type="time"].focus,
    input[data-datepicker-mode="time"]:focus,
    input[data-datepicker-mode="time"].focus {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 9 9'%3e%3cpath d='M4.5.5c-2.2 0-4 1.8-4 4s1.8 4 4 4 4-1.8 4-4-1.8-4-4-4zm1.8 5.2l-.4.1-.2-.1-1.4-1-.1-.1v-.1-.1-.1-.1L5 1.3c0-.2.2-.3.4-.2.2.1.3.3.3.5L5 4.3l1.2.8c.1.2.2.4.1.6z' fill='%23109899'/%3e%3c/svg%3e");
    }

    .input-group input[data-datepicker][type="text"]:not(:last-child) {
        max-width: calc(100% - 2.5em);
        float: left;
        border-radius: 0.3rem 0px 0px 0.3rem;
        border-right-width: 0;
        background-image: none;
    }

    .input-group input[data-datepicker][type="text"]:not(:last-child)~button {
        width: 2.5em;
        height: 2.25em;
        float: right;
        cursor: pointer;
        border-radius: 0px 0.3rem 0.3rem 0px;
        padding: 0.25em 0.5em;
        font: inherit;
        line-height: 1.5;
        border: 0.075em solid currentColor;
        border-left-width: 0;
        background-color: currentColor;
        background-repeat: no-repeat;
        background-size: 1.25em 1.25em;
        background-position: calc(100% - 0.55em) center;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 9 9'%3e%3cpath d='M1.8.5v.8h-.7c-.2 0-.4.2-.4.4v6.5c0 .2.2.4.4.4H8c.2 0 .4-.2.4-.4V1.6c0-.2-.2-.4-.4-.4h-.8V.5H6v.8H3V.5H1.8zm-.3 3h6.1v4.2H1.5V3.5zm.3.4v1.5h1.5V3.9H1.8zm1.9 0v1.5h1.5V3.9H3.7zm1.9 0v1.5h1.5V3.9H5.6zM1.8 5.8v1.5h1.5V5.8H1.8zm1.9 0v1.5h1.5V5.8H3.7z' fill='%23fff'/%3e%3c/svg%3e");
        z-index: 1;
        outline: 0;
        will-change: color, background, box-shadow;
        transition: color 0.2s ease, box-shadow 0.2s ease,
            background 0.2s ease;
    }

    .input-group input[data-datepicker][type="text"]:not(:last-child)~button:focus {
        color: #109899;
        box-shadow: 0 0 0 0.2rem rgba(16, 152, 153, 0.25);
        z-index: 2;
    }

    .datepicker {
        position: absolute;
        display: inline-block;
        margin: 0;
        padding: 0;
        overflow: visible;
        width: 20rem;
        font-size: 1rem;
        color: #222;
        background-color: #fff;
        border-radius: 0.3rem;
        border: 1px solid #e7e9ed;
        box-shadow: 0 0.3rem 1rem 0 rgba(0, 0, 0, 0.15);
        outline: none;
        z-index: 1020;
        will-change: opacity, transform;
        transition: opacity 0.2s linear, transform 0.2s ease;
        opacity: 0;
    }

    .datepicker,
    .datepicker * {
        -webkit-tap-highlight-color: transparent;
    }

    .datepicker,
    .datepicker *:not(input):not(textarea) {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    .datepicker [hidden] {
        display: none !important;
        visibility: hidden;
    }

    .datepicker:not(.show) {
        display: none;
        pointer-events: none;
    }

    .datepicker.show {
        display: block;
        opacity: 1;
    }

    .datepicker.slide-in {
        transition: opacity 0.2s ease, transform 0.2s linear;
    }

    .datepicker.slide-in:not(.show) {
        opacity: 0;
        transform: translate(0, 0);
    }

    .datepicker.modal {
        position: absolute;
        top: 10%;
        left: 50%;
        transform: translateX(-50%);
        border: 0;
        width: 20rem;
    }

    .datepicker.modal .indicator {
        display: none;
    }

    .datepicker.calendar .week abbr::before {
        content: attr(data-twochar) !important;
    }

    @media (min-width: 42rem) {
        .datepicker.calendar {
            width: 40rem;
        }

        .datepicker.calendar>div {
            display: block;
            width: 50.5%;
            float: right;
        }

        .datepicker.calendar .datepicker-header {
            position: absolute;
            bottom: 0;
            top: 0;
            left: 0;
            height: 100%;
            width: 49.5%;
            border-radius: 0.3rem 0 0 0.3rem;
        }

        .datepicker.calendar .datepicker-header time {
            position: absolute;
            font-size: 1.2rem;
            bottom: 1rem;
        }

        .datepicker.calendar .datepicker-navigation {
            clear: left;
        }
    }

    .datepicker-backdrop {
        position: fixed;
        background: rgba(0, 0, 0, 0.25);
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        z-index: 1019;
    }

    .datepicker.modal~.indicator {
        display: none;
    }

    .datepicker .indicator {
        position: absolute;
        display: block;
        top: auto;
        right: auto;
        bottom: -0.5rem;
        left: calc(50% - 0.5rem);
        transform: rotate(-45deg);
        width: 1rem;
        height: 1rem;
        background: #fff;
        border: 1px solid #d8dce2;
        border-width: 0 0 1px 1px;
        box-shadow: 0rem 0rem 0.75rem 2px rgba(0, 0, 0, 0.15);
        z-index: -1;
    }

    .datepicker.bottom .indicator {
        top: -0.5rem;
        bottom: auto;
        border-color: #e7e9ed;
        border-width: 1px 1px 0 0;
        box-shadow: none;
    }

    .datepicker.left .indicator {
        top: calc(50% - 0.5rem);
        right: -0.5rem;
        left: auto;
        bottom: auto;
        border-width: 1px 0 0 1px;
    }

    .datepicker.right .indicator {
        top: calc(50% - 0.5rem);
        right: auto;
        left: -0.5rem;
        bottom: auto;
        border-width: 0 1px 0 1px;
    }

    .datepicker button,
    .datepicker input,
    .datepicker time,
    .datepicker a {
        will-change: color, background, box-shadow, border, font;
        transition: color 0.2s ease, background 0.2s ease,
            box-shadow 0.2s ease, border 0.2s ease, font 0.2s ease;
    }

    .datepicker button,
    .datepicker input,
    .datepicker a {
        position: relative;
        overflow: visible;
        min-width: 1rem;
        font: inherit;
        color: inherit;
        color: currentColor;
        text-align: center;
        text-decoration: none;
        padding: 0;
        margin: 0;
        border: 0;
        background: transparent;
        line-height: normal;
        border-radius: 0.3rem;
        pointer-events: all;
        outline: 0;
        z-index: 1;
    }

    .datepicker button:focus,
    .datepicker button:active,
    .datepicker input:focus,
    .datepicker input:active,
    .datepicker a:focus,
    .datepicker a:active {
        z-index: 2;
    }

    .datepicker button:active,
    .datepicker input:active,
    .datepicker a:active {
        box-shadow: none;
    }

    .datepicker button svg,
    .datepicker input svg,
    .datepicker a svg {
        pointer-events: fill;
        width: 1em;
        height: 1em;
        fill: currentColor;
    }

    .datepicker input:focus {
        color: #109899;
    }

    .datepicker input:invalid,
    .datepicker input:invalid:required {
        outline: 1px !important;
        outline-color: red !important;
        z-index: 2;
    }

    .datepicker button {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        text-align: center;
        cursor: pointer;
    }

    .datepicker button:first-letter {
        text-transform: uppercase;
    }

    .datepicker button:hover {
        color: #109899;
    }

    .datepicker button:focus {
        color: #fff;
        background-color: #109899;
        box-shadow: 0 0 0 0.3rem rgba(16, 152, 153, 0.15);
    }

    .datepicker button:active {
        color: #fff;
        background-color: #12afb0;
    }

    .datepicker-header,
    .datepicker-navigation,
    .datepicker-body,
    .datepicker-footer {
        position: relative;
        display: inline-block;
        width: 100%;
        margin: 0;
        padding: 1rem;
        background-color: #fff;
        z-index: 2;
    }

    .datepicker-header {
        background: #109899;
        border-radius: 0.3rem 0.3rem 0 0;
    }

    .datepicker-header:hover button,
    .datepicker-header:focus button {
        opacity: 1;
        visibility: visible;
    }

    .datepicker-header h2,
    .datepicker-header time {
        color: #fff;
    }

    .datepicker-header h2:first-letter,
    .datepicker-header time:first-letter {
        text-transform: uppercase;
    }

    .datepicker-header h2 {
        font-weight: 400;
        width: 100%;
        font-variant-numeric: ordinal;
        padding: 0.25em 0 0 0;
        margin: 0 0 2rem 0;
    }

    .datepicker-header time {
        color: #fff;
        font-weight: 200;
        font-size: 1.15rem;
    }

    .datepicker-header button {
        opacity: 0;
        visibility: 0;
        color: rgba(255, 255, 255, 0.3);
    }

    .datepicker-header button:hover {
        color: #fff;
    }

    .datepicker-header button:focus {
        opacity: 1;
        visibility: visible;
    }

    .datepicker-header button:first-of-type {
        position: absolute;
        font-size: 1.5rem;
        line-height: 0.75em;
        padding: 0.25em;
        right: 1rem;
        top: 1.1rem;
    }

    .datepicker-navigation {
        position: relative;
        padding-top: 1.5rem;
        border-radius: calc(0.3rem - 1px) calc(0.3rem - 1px) 0 0;
    }

    .datepicker-navigation button {
        border-radius: 50%;
        position: absolute;
        width: 2rem;
        height: 2rem;
        padding: 0.5rem;
        line-height: 1;
        top: 1.3rem;
    }

    .datepicker-navigation button.prev {
        left: 1rem;
    }

    .datepicker-navigation button.next {
        right: 1rem;
    }

    .datepicker-navigation button svg {
        width: 1em;
        height: 1em;
    }

    .datepicker-navigation h3,
    .datepicker-navigation a {
        margin: 0 auto;
        padding: 0;
        line-height: 1.5;
        text-align: center;
        border-radius: 0.3rem;
        font-size: 1.1em;
        width: calc(100% - 5em);
        cursor: pointer;
        font-variant-numeric: tabular-nums;
        outline: 0;
    }

    .datepicker-navigation h3:hover,
    .datepicker-navigation a:hover {
        color: #109899;
    }

    .datepicker-navigation h3:focus,
    .datepicker-navigation a:focus {
        color: #fff;
        background-color: #109899;
        box-shadow: 0 0 0 2px rgba(16, 152, 153, 0.1);
    }

    .datepicker-navigation h3:first-letter,
    .datepicker-navigation a:first-letter {
        text-transform: uppercase;
    }

    .datepicker-body {
        padding-bottom: 1rem;
        border-radius: 0 0 calc(0.3rem - 1px) calc(0.3rem - 1px);
    }

    .datepicker-footer {
        padding-top: 0;
        padding-bottom: 1.5rem;
        text-align: center;
        border-radius: 0 0 calc(0.3rem - 1px) calc(0.3rem - 1px);
    }

    .datepicker-footer button {
        padding: 0.25rem;
        float: none;
    }

    .datepicker .time,
    .datepicker .day,
    .datepicker .month,
    .datepicker .year,
    .datepicker .decade,
    .datepicker .settings {
        display: inline-block;
        min-height: 13rem;
        overflow: visible;
        will-change: opacity, transform;
        transition: opacity 0.3s linear, transform 0.15s ease;
    }

    .datepicker .time:not(.show),
    .datepicker .day:not(.show),
    .datepicker .month:not(.show),
    .datepicker .year:not(.show),
    .datepicker .decade:not(.show),
    .datepicker .settings:not(.show) {
        opacity: 0;
        visibility: hidden;
        transform: translateY(-100%);
    }

    .datepicker .time.show,
    .datepicker .day.show,
    .datepicker .month.show,
    .datepicker .year.show,
    .datepicker .decade.show,
    .datepicker .settings.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0%);
    }

    .datepicker .week,
    .datepicker .days {
        display: inline-block;
        margin: 0;
        padding: 0;
        font-size: 0.9em;
    }

    .datepicker .week abbr,
    .datepicker .week a,
    .datepicker .days abbr,
    .datepicker .days a {
        display: inline-block;
        overflow: hidden;
        width: 14.28%;
        max-width: 14.28%;
        margin: 0;
        text-decoration: none;
        text-align: center;
    }

    .datepicker .week {
        padding-bottom: 0;
    }

    .datepicker .week abbr {
        position: relative;
        padding: 0;
        overflow: hidden;
        color: transparent;
    }

    .datepicker .week abbr:first-letter {
        text-transform: uppercase;
    }

    .datepicker .week abbr::before {
        position: absolute;
        left: 0;
        display: inline-block;
        width: 100%;
        content: attr(data-content);
        color: rgba(128, 128, 128, 0.25);
        text-align: center;
        text-transform: uppercase;
    }

    .datepicker .days {
        position: relative;
    }

    .datepicker .days a {
        line-height: 2;
        z-index: 1;
    }

    .datepicker .days a::after {
        content: none;
        position: absolute;
        bottom: 0.4rem;
        right: 0.2rem;
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        border: 1px solid #fff;
        background: #e77;
    }

    .datepicker .days a.today::after {
        content: "";
        background: orange;
    }

    .datepicker .days a time {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        line-height: 2.2;
        letter-spacing: -0.1em;
    }

    .datepicker .days a:hover span,
    .datepicker .days a:focus span {
        transform: scale(1) translate(-50%, -100%);
        opacity: 1;
        visibility: visible;
    }

    .datepicker .days a span {
        position: absolute;
        left: 50%;
        display: inline-block;
        text-align: left;
        padding: 0.25em 1em;
        transform-origin: left center;
        transform: scale(0.5) translate(-50%, -200%);
        max-width: 20rem;
        color: #fff;
        font-size: 0.8rem;
        background-color: rgba(0, 0, 0, 0.75);
        border-radius: 0.15rem;
        z-index: 2;
        opacity: 0;
        visibility: hidden;
        will-change: opacity, visibility, transform;
        transition: opacity 0.15s linear, transform 0.15s ease-in-out;
    }

    .datepicker .days a span::after {
        content: "";
        position: absolute;
        display: block;
        top: auto;
        right: auto;
        bottom: -0.25rem;
        left: calc(50% - 0.3333333333rem);
        transform: rotate(-45deg);
        width: 0.5rem;
        height: 0.5rem;
        background: rgba(0, 0, 0, 0.75);
        border: 1px solid #d8dce2;
        border-width: 0 0 1px 1px;
        z-index: -1;
    }

    .datepicker .days a span i {
        display: inline-block;
        white-space: nowrap;
        width: 100%;
        font-style: normal;
    }

    .datepicker .time {
        white-space: nowrap;
    }

    .datepicker .time div {
        font-size: 2rem;
        position: relative;
        display: inline-block;
        width: 50%;
        text-align: center;
    }

    .datepicker .time div:first-of-type {
        float: left;
    }

    .datepicker .time div:first-of-type::after {
        content: ":";
        position: absolute;
        width: 1em;
        height: 1.5em;
        right: -0.5em;
        top: calc(50% - 0.25em);
        line-height: 1.5;
    }

    .datepicker .time div:last-of-type {
        float: right;
    }

    .datepicker .time label {
        display: block;
        width: 100%;
        font-size: 0.5em;
        margin: 0 0 1rem 0;
    }

    .datepicker .time label:first-letter {
        text-transform: uppercase;
    }

    .datepicker .time button,
    .datepicker .time input {
        width: 2.5em;
        line-height: 1;
        margin: 0 auto;
        text-align: center;
        float: none;
        border: 2px solid #e7e9ed;
    }

    .datepicker .time button {
        display: block;
        background-color: #f2f2f2;
        border-width: 2px 2px 0 2px;
    }

    .datepicker .time button:hover {
        background-color: #b3b3b3;
        color: #fff;
    }

    .datepicker .time button:focus {
        color: #fff;
        border-color: #109899;
        background-color: #109899;
    }

    .datepicker .time button:active {
        background-color: #12afb0;
    }

    .datepicker .time button:first-of-type {
        border-width: 2px 2px 0 2px;
        border-radius: 0.3rem 0.3rem 0 0;
    }

    .datepicker .time button:last-of-type {
        border-width: 0 2px 2px 2px;
        border-radius: 0 0 0.3rem 0.3rem;
    }

    .datepicker .time input {
        font-variant-numeric: tabular-nums;
        -webkit-appearance: none;
        appearance: none;
        /* autoprefixer: ignore next */
        -moz-appearance: textfield;
        padding: 0.25em 0;
        border-width: 0 2px 0 2px;
        border-radius: 0;
    }

    .datepicker .time input::-webkit-spin-button,
    .datepicker .time input::-webkit-inner-spin-button,
    .datepicker .time input::-webkit-outer-spin-button {
        display: none;
        margin: 0;
    }

    .datepicker .year a,
    .datepicker .decade a {
        line-height: 1;
        width: 33.333%;
    }

    .datepicker .year a time,
    .datepicker .decade a time {
        width: calc(100% - 0.5rem);
        padding: 1.3rem 0.25rem;
        line-height: 1rem;
        border-radius: 0.3rem;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .datepicker .year time {
        font-size: 0.8em !important;
        letter-spacing: 0;
    }

    .datepicker .decade a:last-child {
        margin: 0 33.333%;
    }

    .datepicker .decade time {
        font-size: 1em;
    }

    .datepicker .days a,
    .datepicker .year a,
    .datepicker .decade a {
        position: relative;
        display: inline-block;
        overflow: visible;
        background: transparent !important;
    }

    .datepicker .days a:hover time,
    .datepicker .year a:hover time,
    .datepicker .decade a:hover time {
        color: #333;
        background-color: rgba(51, 51, 51, 0.1);
    }

    .datepicker .days a:focus time,
    .datepicker .year a:focus time,
    .datepicker .decade a:focus time {
        font-weight: bold;
        background-color: #b3b3b3;
        color: #fff;
        box-shadow: 0 0 0 0.25rem rgba(51, 51, 51, 0.05);
    }

    .datepicker .days a time,
    .datepicker .year a time,
    .datepicker .decade a time {
        display: block;
        margin: 0.25rem;
        display: inline-block;
        font-variant-numeric: slashed-zero tabular-nums;
    }

    .datepicker .days a.today:focus time,
    .datepicker .days a.current:focus time,
    .datepicker .year a.today:focus time,
    .datepicker .year a.current:focus time,
    .datepicker .decade a.today:focus time,
    .datepicker .decade a.current:focus time {
        box-shadow: 0 0 0 0.25rem rgba(16, 152, 153, 0.15);
    }

    .datepicker .days a.today time,
    .datepicker .days a.current time,
    .datepicker .year a.today time,
    .datepicker .year a.current time,
    .datepicker .decade a.today time,
    .datepicker .decade a.current time {
        font-weight: bold;
        background-color: #109899;
        color: #fff;
    }

    .datepicker .days a[aria-selected]:focus time,
    .datepicker .year a[aria-selected]:focus time,
    .datepicker .decade a[aria-selected]:focus time {
        box-shadow: 0 0 0 0.25rem rgba(238, 119, 119, 0.15);
    }

    .datepicker .days a[aria-selected] time,
    .datepicker .year a[aria-selected] time,
    .datepicker .decade a[aria-selected] time {
        background-color: #e77;
        color: #fff;
    }

    .datepicker .days a[aria-selected].start::before,
    .datepicker .days a[aria-selected].end::before,
    .datepicker .year a[aria-selected].start::before,
    .datepicker .year a[aria-selected].end::before,
    .datepicker .decade a[aria-selected].start::before,
    .datepicker .decade a[aria-selected].end::before {
        content: "";
        position: absolute;
        height: 100%;
        width: 50%;
        background-color: #fadbdb;
        z-index: -1;
    }

    .datepicker .days a[aria-selected].start::before,
    .datepicker .year a[aria-selected].start::before,
    .datepicker .decade a[aria-selected].start::before {
        right: 0;
    }

    .datepicker .days a[aria-selected].start time,
    .datepicker .year a[aria-selected].start time,
    .datepicker .decade a[aria-selected].start time {
        border-radius: 50% 0 0 50%;
    }

    .datepicker .days a[aria-selected].start~a,
    .datepicker .year a[aria-selected].start~a,
    .datepicker .decade a[aria-selected].start~a {
        background-color: #fadbdb;
        color: #e77;
    }

    .datepicker .days a[aria-selected].end,
    .datepicker .year a[aria-selected].end,
    .datepicker .decade a[aria-selected].end {
        background-color: inherit !important;
    }

    .datepicker .days a[aria-selected].end::before,
    .datepicker .year a[aria-selected].end::before,
    .datepicker .decade a[aria-selected].end::before {
        left: 0;
    }

    .datepicker .days a[aria-selected].end time,
    .datepicker .year a[aria-selected].end time,
    .datepicker .decade a[aria-selected].end time {
        border-radius: 0 50% 50% 0;
    }

    .datepicker .days a[aria-selected].end~a,
    .datepicker .year a[aria-selected].end~a,
    .datepicker .decade a[aria-selected].end~a {
        background-color: inherit;
        color: inherit;
    }

    .datepicker .days a[disabled],
    .datepicker .year a[disabled],
    .datepicker .decade a[disabled] {
        border-radius: 0;
        pointer-events: none;
        cursor: not-allowed;
        opacity: 0.3;
        filter: grayscale(1);
    }

    .datepicker .settings fieldset,
    .datepicker .day fieldset {
        display: inline-block;
        padding: 0;
        overflow: visible;
    }

    .datepicker .settings label,
    .datepicker .day label {
        display: inline-block;
        position: relative;
        width: 100%;
        line-height: 1;
        padding: 0;
        margin: 0 0 2em 0;
        white-space: normal;
        float: none;
    }

    .datepicker .settings label:after,
    .datepicker .day label:after {
        content: "";
        height: 100%;
    }

    .datepicker .settings label b,
    .datepicker .day label b {
        display: inline-block;
        width: 100%;
    }

    .datepicker .settings label small,
    .datepicker .day label small {
        display: inline-block;
        width: 100%;
        font-weight: normal;
    }

    .datepicker .settings label input,
    .datepicker .day label input {
        position: absolute;
        right: 0;
        top: 0;
        width: 2em;
        line-height: 1;
        -webkit-appearance: checkbox;
        -moz-appearance: checkbox;
        appearance: checkbox;
    }

    .datepicker .settings label input:focus,
    .datepicker .settings label input:checked,
    .datepicker .day label input:focus,
    .datepicker .day label input:checked {
        background-color: #109899;
    }
</style>
<div id="custom_datetime">
    <div class="mb-5 d-flex align-items-center">
        <button type="button" class="btn btn-primary circle me-3 prevStep" style="height: 40px; width:40px">
            <i class="fa-solid fa-arrow-left"></i>
        </button>

        <h4 style="font-weight: 800">Date & Time</h4>
    </div>
    <div class="d-flex justify-content-around">

        <div class="mb-4">
            <div>
                <span for="preferred_date">Preferred Date</span>
            </div>
            <div>
                <input type="date" id="preferred_date" style="min-width: 200px" placeholder="Select a date"
                    name="preferred_date" {{-- data-datepicker-events="{date: '2020-07-12T12:00', name: 'Business Lunch Zürich', type: 'holiday'}"  --}} />
            </div>
            <small class="text-danger error-msg" data-error-for="preferred_date"></small>
        </div>
        <div>
            <div>
                <span for="preferred_time">Preferred Time</span>
            </div>
            <div>
                <input type="time" name="preferred_time" id="preferred_time" style="min-width: 200px;"
                    placeholder="Select a time" />
            </div>
            <small class="text-danger error-msg" data-error-for="preferred_time"></small>
        </div>

    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function(e) {

        var pickerOptions = {
            disablePast: true,
            disableFuture: true,
            disableWeekend: false,
            useClock: false,
            weekStart: 1,
            minStep: 15,
            hrsStep: 1,
            enabledDates: ['2025-07-15', '2025-07-18', '2025-07-20'], // dates must match formatDate output 
            // minDate: '2025-07-10', // example ISO date string, set your desired min date here
            // maxDate: '2025-07-20', // example ISO date string, set your desired max date here
            // holidays: [{date: '12-25', name: 'Weihnachten'}, {date: '01-01', name: 'Neujahr'}]
        };

        var pickerTranslation = {
            minutes: "Minuten",
            hours: "Stunden",
            settime: "Uhrzeit stellen",
            months: [
                "Januar",
                "Febuar",
                "März",
                "April",
                "Mai",
                "Juni",
                "Juli",
                "August",
                "September",
                "Oktober",
                "November",
                "Dezember",
            ],
            days: [
                "Montag",
                "Dienstag",
                "Mittwoch",
                "Donnerstag",
                "Freitag",
                "Samstag",
                "Sonntag",
            ],
            format: "yyyy-MM-dd hh:mm",
            longformat: "d. MMMM yyyy",
            displayformat: "D, d. MMMM yyyy",
            calendar: "Kalender",
            previous: "vorheriger",
            next: "nächster",
            close: "schliessen",
            settings: "Einstellungen",
            selected: "ausgewählt",
            select: "auswählen",
            today: "heute",
            set: "wählen",
            now: "jetzt",
        };
        var now = new Date(); // Generate current event dates for presentation
        var pickerDates = [
            // {
            //     date: now.getFullYear() +
            //         "-" +
            //         ("0" + (now.getMonth() + 1)).slice(-2) +
            //         "-14",
            //     name: "Kalendereintrag",
            //     type: "work",
            // },
            // {
            //     date: now.getFullYear() +
            //         "-" +
            //         ("0" + (now.getMonth() + 1)).slice(-2) +
            //         "-14",
            //     name: "Kalendereintrag II",
            //     type: "private",
            // },
            // {
            //     date: now.getFullYear() +
            //         "-" +
            //         ("0" + (now.getMonth() + 2)).slice(-2) +
            //         "-21",
            //     name: "Kalendereintrag III",
            //     type: "private",
            // },
        ];
        // Options / translation de-CH:
        Datepicker(pickerOptions, false, pickerDates); //
    })

    function Datepicker(opt, lang, evts) {
        var w = window,
            d = document,
            options = {
                disablePast: false,
                disableFuture: false,
                disableWeekend: true,
                weekStart: 0,
                useClock: false,
                minStep: 15,
                hrsStep: 1,
                holidays: [{
                        date: "01-01",
                        name: "New Year"
                    },
                    {
                        date: "12-25",
                        name: "Christmas"
                    },
                ],
            },
            i18n = {
                minutes: "minutes",
                hours: "hours",
                settime: "Set time",
                months: [
                    "January",
                    "Febuary",
                    "March",
                    "April",
                    "May",
                    "June",
                    "July",
                    "August",
                    "September",
                    "October",
                    "November",
                    "December",
                ],
                days: [
                    "Monday",
                    "Tuesday",
                    "Wednesday",
                    "Thursday",
                    "Friday",
                    "Saturday",
                    "Sunday",
                ],
                format: "yyyy-MM-dd hh:mm",
                longformat: "MMMM d, yyyy",
                displayformat: "D, MMMM d yyyy",
                calendar: "calendar",
                previous: "previous",
                next: "next",
                close: "close",
                settings: "settings",
                selected: "selected",
                select: "select",
                today: "today",
                set: "set",
                now: "now",
            },
            keys = {
                esc: 27,
                space: 32,
                pgup: 33,
                pgdown: 34,
                end: 35,
                home: 36,
                left: 37,
                up: 38,
                right: 39,
                down: 40,
            },
            icons = {
                left: '<path d="M2.4,4l2.8-2.8L4.5,0.5l-4,4l4,4l0.7-0.7L2.4,5h6.1V4H2.4z"/>',
                right: '<path d="M0.5,4v1h6.1L3.8,7.8l0.7,0.7l4-4l-4-4L3.8,1.2L6.6,4H0.5z"/>',
                calendar: '<path d=""/>',
                clock: '<path d=""/>',
                close: '<path d="M8.4 1.4L7.6.6 4.5 3.8 1.4.6l-.8.8 3.2 3.1L.6 7.6l.8.8 3.1-3.2 3.1 3.2.8-.8-3.2-3.1z"/>',
                settings: '<path d="M2.5,5.5H1.8v3H1.3v-3H0.5v-1h2V5.5z M1.8,0.5H1.3V4h0.5V0.5z M5.5,3h-2v1h0.8v4.5h0.5V4h0.8V3z M4.8,0.5H4.3v2h0.5V0.5z M8.5,6.5h-2v1h0.8v1h0.5v-1h0.8V6.5z M7.8,0.5H7.3V6h0.5V0.5z"/>',
            },
            // Global formats
            formats = {
                datetime: "yyyy-MM-dd hh:mm",
                date: "yyyy-MM-dd",
                month: "yyyy-MM",
                time: "hh:mm",
            },
            // Premarked event days & holidays
            events = [];
        // Merge options and language objects
        if (opt) mergeObj(options, opt);
        if (lang) mergeObj(i18n, lang);
        if (opt.icons) mergeObj(icons, opt.icons);
        if (opt.format) mergeObj(formats, opt.format);
        if (evts) mergeObj(events, evts);
        // Layout template
        var tpl = [
            '<i class="indicator" aria-hidden="true"></i>',
            '<div class="datepicker-header" role="header"><h2 aria-live="polite">' +
            i18n.calendar +
            '</h2><time aria-live="polite"></time><button type="button" aria-label="' +
            i18n.settings +
            '" title="' +
            i18n.settings +
            '"><svg viewBox="0 0 9 9" role="presentation">' +
            icons.settings +
            "</svg></button></div>",
            '<div class="datepicker-navigation" role="navigation">',
            '  <button type="button" class="prev" aria-label="' +
            i18n.previous +
            '"><svg viewBox="0 0 9 9" role="presentation">' +
            icons.left +
            "</svg></button>",
            '  <h3 id="datepicker-label" aria-live="polite" tabindex="0">Month Year</h3>',
            '  <button type="button" class="next" aria-label="' +
            i18n.next +
            '"><svg viewBox="0 0 9 9" role="presentation">' +
            icons.right +
            "</svg></button>",
            "</div>",
            '<div class="datepicker-body" role="document">',
            '  <form class="time" hidden>',
            '   <div class="hours"><label>' + i18n.hours + "</label>",
            '    <button type="button" role="spinbutton">&plus;</button><input type="number" step="' +
            options.hrsStep +
            '" min="0" max="23"/><button type="button" role="spinbutton">&minus;</button>',
            "   </div>",
            '   <div class="minutes"><label>' +
            i18n.minutes +
            "</label>",
            '    <button type="button" role="spinbutton">&plus;</button><input type="number" step="' +
            options.minStep +
            '" min="0" max="59"/><button type="button" role="spinbutton">&minus;</button>',
            "   </div>",
            "  </form>",
            '  <div class="day" hidden></div>',
            '  <div class="month" role="grid" aria-labelledby="datepicker-label" hidden>',
            '   <div class="week" scope="col"></div>',
            '   <div class="days" scope="col"></div>',
            "  </div>",
            '  <div class="year" hidden><div class="months"></div></div>',
            '  <div class="decade" hidden><div class="years"></div></div>',
            '  <div class="settings" hidden></div>',
            "</div>",
            '<div class="datepicker-footer" role="footer"><button type="button">' +
            i18n.select +
            "</button>",
            "</div>",
        ].join("");
        // Trigger selectors
        var triggers = d.querySelectorAll(
            '[type*="date"], [type="month"], [type="time"], [type="week"], [data-datepicker]'
        );
        [].slice.call(triggers).forEach(function(trigger) {
            init(trigger);
        });
        // Datepicker
        function init(trigger) {
            var picker,
                indicator,
                header,
                footer,
                navigation,
                title,
                current,
                time,
                minutes,
                hours,
                day,
                days,
                week,
                month,
                year,
                decade,
                settings,
                label,
                pButton,
                nButton,
                aButton,
                sButton;
            var type =
                trigger.getAttribute("type").toLowerCase() !==
                ("text" || "hidden") ?
                trigger.getAttribute("type") :
                trigger.getAttribute("data-datepicker") || "date";
            type = type.split("-")[0].toLowerCase();
            var mode =
                trigger.getAttribute("data-datepicker-mode") ||
                "single",
                view =
                trigger.getAttribute("data-datepicker-view") ||
                type,
                offset =
                trigger.getAttribute("data-datepicker-offset") || 5,
                date = new Date(),
                today = new Date(),
                selected = [],
                altTrigger = null;
            var id =
                "datepicker-" +
                (trigger.id ?
                    trigger.id :
                    Math.random().toString(36).substr(2, 9));
            // Trigger text input pattern
            if (trigger.type.match(/text/gi)) {
                trigger.setAttribute(
                    "pattern",
                    "([-+\u2013\u2014]|[0-9]+|[/-:]|[A-Z]+)+"
                );
                if (type === "datetime")
                    trigger.setAttribute(
                        "pattern",
                        "[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}"
                    );
            }
            // Additional picker trigger
            if (trigger.type.match(/hidden|text/gi)) {
                altTrigger = trigger.parentElement.querySelector(
                    '[for="' + trigger.id + '"], button'
                );
                if (altTrigger) altTrigger.onclick = toggle;
            }
            trigger.setAttribute("aria-haspopup", true);
            trigger.setAttribute("aria-expanded", false);
            trigger.setAttribute("aria-controls", id);
            // Trigger event listener (overwrite/block existing)
            trigger.onfocus = prevent;
            trigger.oninput = prevent;
            trigger.onclick = toggle;
            createPicker();
            // Picker methods
            // Pick a date / datefunction
            function pick(e) {
                if (e) e.preventDefault();
                var el = this,
                    dt = new Date(
                        el.firstChild.getAttribute("datetime")
                    ),
                    val,
                    string = formatDate(dt, formats.date),
                    isSelected = el.hasAttribute("aria-selected");
                switch (type) {
                    case "datetime":
                        val = formatDate(dt, formats.datetime)
                            .trim()
                            .replace(" ", "T");
                        break;
                    case "time":
                        val = formatDate(dt, formats.time);
                        break;
                    case "month":
                        val = formatDate(dt, formats.month);
                        break;
                    default:
                        val = string;
                }
                if (view.match(/(decade|year|month)/g)) {
                    date.setFullYear(dt.getFullYear());
                    if (view === "year") date.setMonth(dt.getMonth());
                    if (view === "month") date.setDate(dt.getDate());
                    if (view === "year" && type !== "month")
                        monthView();
                    else if (view === "decade" && type !== "year")
                        yearView();
                    else if (view === "month" && type === "datetime")
                        timeView();
                    else
                        isSelected ?
                        deselect(el, string, val) :
                        select(el, string, val);
                } else
                    isSelected ?
                    deselect(el, string, val) :
                    select(el, string, val);
            }
            // Toggle datepicker ui
            function toggle(e) {
                if (e) e.preventDefault();
                var willShow = !picker.className.match(/\bshow\b/g),
                    ops = d.querySelectorAll(".datepicker.show"),
                    fcs = d.querySelectorAll("input.focus");
                [].slice.call(ops).forEach(function(o) {
                    o.className = o.className
                        .replace(/\bshow\b/g, "")
                        .trim();
                });
                [].slice.call(fcs).forEach(function(f) {
                    f.className = f.className
                        .replace(/\bfocus\b/g, "")
                        .trim();
                });
                picker.className = willShow ?
                    picker.className.trim() + " show" :
                    picker.className.replace(/\bshow\b/g, "").trim();
                trigger.setAttribute(
                    "aria-expanded",
                    willShow ? true : false
                );
                trigger.className = willShow ?
                    trigger.className.trim() + " focus" :
                    trigger.className
                    .replace(/\bfocus\b/g, "")
                    .trim();
                // Check for existing / default date
                var value =
                    trigger.value != trigger.defaultValue ?
                    trigger.value :
                    null;
                // Reset & prepare selected array
                if (
                    willShow &&
                    value &&
                    type.match(/(multiple|range)/g)
                ) {
                    value
                        .replace(/(\\|\/|\.)/g, "-")
                        .replace(/\u2013|\u2014/g, ",")
                        .trim()
                        .split(",")
                        .forEach(function(val) {
                            arrAddValue(selected, val);
                        });
                } else selected = value ? [value] : [];
                // Display
                if (willShow) {
                    switch (type) {
                        case "time":
                            timeView();
                            break;
                        case "month":
                            yearView();
                            break;
                        case "year":
                            decadeView();
                            break;
                        default:
                            monthView();
                    }
                }
                if (mode.match(/(calendar|modal)/gi))
                    backdrop(willShow);
                place(picker, trigger);
                trigger.blur(); // IE fix & prevent software keyboard
            }
            // Select a date
            function select(el, string, val) {
                if (mode.match(/(range)/gi)) {
                    selected = arrAddValue(selected, string);
                    trigger.value = selected.join(" - ");
                } else if (mode.match(/(multiple)/gi)) {
                    trigger.value = selected.join(", ");
                } else {
                    selected = [string];
                    [].slice
                        .call(
                            picker.querySelectorAll("[aria-selected]")
                        )
                        .forEach(function(l) {
                            l.removeAttribute("aria-selected");
                        });
                    trigger.value = val;
                    toggle();
                }
                el.title = ucFirst(i18n.selected);
                el.setAttribute("aria-selected", true);
            }
            // Deselect a date
            function deselect(el, string, val) {
                if (string)
                    selected = selected.filter(function(
                        v,
                        i,
                        selected
                    ) {
                        return v === string;
                    });
                if (!el || !mode.match(/(multiple|range)/gi))
                    selected.forEach(function(ref) {
                        el = picker.querySelector(
                            '[href="#' + ref + '"]'
                        );
                        if (el) {
                            el.removeAttribute("aria-selected");
                            el.removeAttribute("title");
                        }
                        trigger.value = "";
                    });
                else {
                    el.removeAttribute("aria-selected");
                    el.removeAttribute("title");
                }
                el.blur();
            }
            // Item methods
            function disable(el) {
                el.setAttribute("draggable", false);
                el.setAttribute("disabled", "");
                el.setAttribute("tabindex", "-1");
            }

            function enable(el) {
                el.setAttribute("draggable", true);
                el.removeAttribute("disabled", "");
                el.onclick = pick;
                el.ondrag = drag;
                trigger.ondragover = over;
                trigger.ondrop = drop;

                function drag(e) {
                    e.dataTransfer.dropEffect = "link";
                }

                function over(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = "copy";
                }

                function drop(e) {
                    e.preventDefault();
                    el.click();
                }
            }

            function isSelected(string, matchType) {
                var match = false;
                if (selected && selected.length > 0)
                    selected.forEach(function(item) {
                        if (
                            matchType === "year" &&
                            item.slice(0, 4) === string.slice(0, 4)
                        )
                            match = true;
                        else if (
                            matchType === "month" &&
                            item.slice(0, 8) === string.slice(0, 8)
                        )
                            match = true;
                        else if (
                            item.slice(0, 10) === string.slice(0, 10)
                        )
                            match = true;
                    });
                return match;
            }

            function hasEvents(string, matchType) {
                var matches = [];
                if (events && events.length > 0)
                    events.forEach(function(ev, i) {
                        if (
                            matchType === "year" &&
                            ev["date"].slice(0, 4) ===
                            string.slice(0, 4)
                        )
                            matches[i] = ev;
                        else if (
                            matchType === "month" &&
                            ev["date"].slice(0, 8) ===
                            string.slice(0, 8)
                        )
                            matches[i] = ev;
                        else if (
                            ev["date"].slice(0, 10) ===
                            string.slice(0, 10)
                        )
                            matches[i] = ev;
                    });
                return matches;
            }
            // Create datepicker
            function createDay(num, dd, yyyy) {
                var el = d.createElement("a"),
                    ti = d.createElement("time"),
                    string = formatDate(date, formats.date), // e.g., '2025-07-15'
                    isToday = date.toString() === today.toString(),
                    dayEvents = hasEvents(string);

                ti.innerHTML = num;
                el.href = "#" + string;
                ti.title = formatDate(date, i18n.displayformat);
                ti.setAttribute("datetime", string);

                if (num === 1) {
                    if (dd === 0) el.style.marginLeft = 6 * 14.28 + "%";
                    else el.style.marginLeft = (dd - 1) * 14.28 + "%";
                }

                // Parse minDate and maxDate as Date objects
                var minDate = pickerOptions.minDate ? new Date(pickerOptions.minDate) : null;
                var maxDate = pickerOptions.maxDate ? new Date(pickerOptions.maxDate) : null;

                // If enabledDates exists, enable only those dates
                if (pickerOptions.enabledDates && Array.isArray(pickerOptions.enabledDates)) {
                    if (!pickerOptions.enabledDates.includes(string)) {
                        disable(el);
                    } else {
                        enable(el);
                    }
                }
                // Otherwise enforce minDate and maxDate limits
                else if (
                    (minDate && date < minDate) ||
                    (maxDate && date > maxDate)
                ) {
                    disable(el);
                } else if (pickerOptions.disablePast && date.getTime() <= today.getTime() - 1) {
                    disable(el);
                } else if (pickerOptions.disableWeekend && (dd === 0 || dd === 6)) {
                    disable(el);
                } else {
                    enable(el);
                }

                if (isToday) {
                    el.className += " today";
                    ti.title = ucFirst(i18n.today);
                    ti.setAttribute("aria-current", "date");
                    ti.setAttribute("datetime", string);
                }

                el.appendChild(ti);

                if (dayEvents && dayEvents.length > 0) {
                    var tip = d.createElement("span");
                    dayEvents.forEach(function(event) {
                        var evt = d.createElement("i");
                        evt.innerHTML = ucFirst(event.name);
                        evt.setAttribute("datetime", event.date);
                        evt.setAttribute("data-type", event.type ? event.type : "holiday");
                        ti.title += " \n" + evt.title;
                        tip.appendChild(evt);
                    });
                    el.appendChild(tip);
                }

                if (isSelected(string)) el.setAttribute("aria-selected", true);

                days.appendChild(el);
            }


            // Picker backdrop
            function backdrop(show) {
                if (!picker.className.match(/\bmodal\b/g))
                    picker.className += " modal";
                if (
                    mode.match(/(calendar)/gi) &&
                    !picker.className.match(/\bcalendar\b/g)
                )
                    picker.className += " calendar";
                var drop = d.querySelector(".datepicker-backdrop");
                if (!drop) {
                    drop = d.createElement("div");
                    picker.insertAdjacentElement("afterend", drop);
                }
                drop.className = "datepicker-backdrop";
                if (show)
                    drop.onclick = function(e) {
                        toggle(e);
                    };
                show
                    ?
                    drop.removeAttribute("hidden") :
                    drop.setAttribute("hidden", "");
            }
            // Picker placing
            function place(el, tg) {
                if (
                    !el.className.match(/\bshow\b/g) ||
                    mode.match(/(calendar|modal)/gi)
                )
                    return false;
                var x,
                    y,
                    eP = pos(el),
                    tP = pos(tg),
                    iP = indicator ? pos(indicator) : null,
                    sY = w.pageYOffset,
                    sX = w.pageXOffset,
                    vw = d.documentElement.clientWidth,
                    vh = w.innerHeight;
                var S = w.getComputedStyle(tg),
                    fX = S.backgroundPositionX || "50%",
                    fY = "50%";
                x = tP.left + tP.width - eP.width - sX;
                y = tP.top - eP.height - offset - sY;
                el.className = el.className
                    .replace(/\b(left|right|top|bottom)\b/g, "")
                    .trim();
                if (x <= 0) {
                    x = offset;
                    el.className =
                        el.className.replace(/\bleft\b/g, "").trim() +
                        " right";
                }
                if (y <= 0) {
                    y = tP.top + tP.height + offset;
                    el.className += " bottom";
                }
                if (x >= vw)
                    el.className =
                    el.className.replace(/\bright\b/g, "").trim() +
                    " left";
                if (y >= vh)
                    el.className = el.className
                    .replace(/\bbottom\b/g, "")
                    .trim();
                if (iP)
                    indicator.style.left =
                    "calc(100% - " +
                    (iP.width / 2 + tP.width / 2) +
                    "px)";
                el.setAttribute(
                    "style",
                    "left:" + x + "px;top:" + y + "px;"
                );
                // Overwite previous listeners
                w.onscroll = function() {
                    place(el, tg);
                };
                w.onresize = function() {
                    place(el, tg);
                };
                d.onorientationchange = function() {
                    place(el, tg);
                };
                //Position method
                function pos(el) {
                    var b = el.getBoundingClientRect();
                    return {
                        top: b.top + w.pageYOffset,
                        left: b.left + w.pageXOffset,
                        width: b.width,
                        height: b.height,
                    };
                }
            }
            // Picker methods
            function createTime() {
                var mm = minutes.querySelector("input"),
                    hh = hours.querySelector("input");
                mm.value = date.getMinutes();
                hh.value = date.getHours();
                spinner(mm);
                spinner(hh);
                time.addEventListener("change", set, false);

                function set(e) {
                    if (type === "time")
                        trigger.value =
                        hh.value + ":" + mm.value + ":00";
                    trigger.value = hh.value + ":" + mm.value;
                }

                function check(e, ele) {
                    var el = ele ? ele : e.target || this,
                        val = parseInt(el.value, 10),
                        coe = parseInt(el.step, 10),
                        min = parseInt(el.min, 10),
                        max = parseInt(el.max, 10),
                        unit = el.parentElement.className.match(
                            /minutes/g
                        ) ?
                        "mm" :
                        "hh";
                    if (val <= min)
                        el.value =
                        Math.ceil(max / coe) * coe -
                        (unit === "mm" ? coe : 0);
                    else if (val > max)
                        el.value = Math.ceil(min / coe) * coe;
                    else if (isNaN(val))
                        el.value = Math.ceil(min / coe) * coe;
                    el.value = zero(el.value);
                    unit === "mm" ?
                        date.setMinutes(el.value) :
                        date.setHours(el.value);
                }

                function spinner(el) {
                    var step = parseInt(el.step, 10) || 1,
                        plus = el.parentNode.querySelector(
                            "button:first-of-type"
                        ),
                        minus = el.parentNode.querySelector(
                            "button:last-of-type"
                        );
                    check(false, el);
                    plus.onclick = function(e) {
                        change(el, step);
                    };
                    minus.onclick = function(e) {
                        change(el, -step);
                    };
                    el.oninput = check;
                    el.onchange = check;
                }

                function change(el, num) {
                    el.value = zero(parseInt(el.value, 10) + num);
                    "createEvent" in d
                        ?
                        el.dispatchEvent(new Event("change")) :
                        el.fireEvent("onchange");
                }

                function zero(int) {
                    return ("00" + int).slice(-2);
                }
            }

            function createDay(num, dd, yyyy) {
                var el = d.createElement("a"),
                    ti = d.createElement("time"),
                    string = formatDate(date, formats
                        .date), // this should output the date string format like 'YYYY-MM-DD'
                    isToday = date.toString() === today.toString(),
                    dayEvents = hasEvents(string);

                ti.innerHTML = num;
                el.href = "#" + string;
                ti.title = formatDate(date, i18n.displayformat);
                ti.setAttribute("datetime", formatDate(date, formats.date));

                if (num === 1) {
                    if (dd === 0) el.style.marginLeft = 6 * 14.28 + "%";
                    else el.style.marginLeft = (dd - 1) * 14.28 + "%";
                }

                // Check enabledDates list logic
                if (pickerOptions.enabledDates && Array.isArray(pickerOptions.enabledDates)) {
                    if (!pickerOptions.enabledDates.includes(string)) {
                        disable(el);
                    } else {
                        enable(el);
                    }
                } else {
                    // Your existing disable/enable logic if no enabledDates array provided
                    if (pickerOptions.disablePast && date.getTime() <= today.getTime() - 1) {
                        disable(el);
                    } else if (pickerOptions.disableWeekend && (dd === 0 || dd === 6)) {
                        disable(el);
                    } else {
                        enable(el);
                    }
                }

                if (isToday) {
                    el.className += " today";
                    ti.title = ucFirst(i18n.today);
                    ti.setAttribute("aria-current", "date");
                    ti.setAttribute("datetime", formatDate(date, formats.date));
                }

                el.appendChild(ti);

                if (dayEvents && dayEvents.length > 0) {
                    var tip = d.createElement("span");
                    dayEvents.forEach(function(event) {
                        var evt = d.createElement("i");
                        evt.innerHTML = ucFirst(event.name);
                        evt.setAttribute("datetime", event.date);
                        evt.setAttribute("data-type", event.type ? event.type : "holiday");
                        ti.title += " \n" + evt.title;
                        tip.appendChild(evt);
                    });
                    el.appendChild(tip);
                }

                if (isSelected(string)) el.setAttribute("aria-selected", true);

                days.appendChild(el);
            }



            function createMonth() {
                week.innerHTML = "";
                i18n.days.forEach(function(name) {
                    var el = d.createElement("abbr");
                    el.setAttribute("scope", "col");
                    el.setAttribute("title", name);
                    el.setAttribute("draggable", "false");
                    el.setAttribute(
                        "data-content",
                        name.substring(0, 1)
                    );
                    el.setAttribute(
                        "data-twochar",
                        name.substring(0, 2)
                    );
                    el.setAttribute(
                        "data-threechar",
                        name.substring(0, 3)
                    );
                    el.innerHTML = name;
                    week.appendChild(el);
                });
                var cur = date.getMonth();
                date.setDate(1);
                days.innerHTML = "";
                while (date.getMonth() === cur) {
                    createDay(
                        date.getDate(),
                        date.getDay(),
                        date.getFullYear()
                    );
                    date.setDate(date.getDate() + 1);
                }
                // Set date object back
                date.setDate(1);
                date.setMonth(date.getMonth() - 1);
            }

            function createYear() {
                year.firstChild.innerHTML = "";
                i18n.months.forEach(function(name, i) {
                    var a = d.createElement("a"),
                        ti = d.createElement("time"),
                        cur = new Date(date.getFullYear(), i + 1, 0),
                        string = formatDate(cur, formats.date),
                        isCurrent =
                        cur.getMonth() === today.getMonth() &&
                        cur.getFullYear() === today.getFullYear();
                    ti.innerHTML = name;
                    ti.setAttribute(
                        "datetime",
                        formatDate(cur, formats.month)
                    );
                    a.href = "#" + formatDate(cur, formats.month);
                    a.setAttribute(
                        "title",
                        name + " " + cur.getFullYear()
                    );
                    a.setAttribute(
                        "data-content",
                        name.substring(0, 3)
                    );
                    if (isCurrent) a.className += " current";
                    if (isSelected(string, "month"))
                        a.setAttribute("aria-selected", true);
                    a.addEventListener("click", pick, false);
                    a.appendChild(ti);
                    year.firstChild.appendChild(a);
                });
            }

            function createYears(num) {
                var years = [],
                    span = yearSpan(date.getFullYear(), num);
                for (var i = span[0]; i <= span[1]; i++) years.push(i);
                decade.firstChild.innerHTML = "";
                years.forEach(function(name, i) {
                    var a = d.createElement("a"),
                        ti = d.createElement("time"),
                        cur = new Date(name, date.getMonth() + 1, 0),
                        isCurrent = name === today.getFullYear();
                    ti.innerHTML = name;
                    ti.setAttribute("datetime", cur.getFullYear());
                    a.href = "#" + cur.getFullYear();
                    if (isCurrent) a.className += " current";
                    if (isSelected(name.toString(), "year"))
                        a.setAttribute("aria-selected", true);
                    a.addEventListener("click", pick, false);
                    a.appendChild(ti);
                    decade.firstChild.appendChild(a);
                });
            }

            function createSettings() {
                var stpl = [
                    "<fieldset>",
                    '<label><b>Setting</b><small>Setting description.</small><input type="checkbox"/></label>',
                    '<label><b>Setting I</b><small>Setting description I.</small><input type="checkbox"/></label>',
                    "</fieldset>",
                    "",
                ].join("");
                settings.innerHTML = stpl;
            }
            // Views
            // View setter
            function setView() {
                var el = picker.querySelector("." + view),
                    active =
                    el.querySelector(
                        "[aria-selected], .today, .current"
                    ) || el.querySelector("a:not([disabled])"),
                    p = date.getMonth() - 1,
                    pDate = new Date(date.getFullYear(), p, 0),
                    n = date.getMonth() + 1,
                    nDate = new Date(date.getFullYear(), n, 0),
                    isToday =
                    formatDate(date, formats.date) ===
                    formatDate(today, formats.date);
                label.innerHTML =
                    i18n.months[date.getMonth()] +
                    " " +
                    date.getFullYear();
                header.setAttribute("hidden", "");
                navigation.removeAttribute("hidden");
                pButton.removeAttribute("hidden");
                footer.setAttribute("hidden", "");
                aButton.setAttribute("hidden", "");
                sButton.setAttribute("hidden", "");
                nButton.firstChild.innerHTML = icons.right;
                nButton.setAttribute("aria-label", i18n.next);
                if (mode.match(/(calendar)/gi)) {
                    title.innerHTML = formatDate(
                        today,
                        i18n.displayformat
                    );
                    header.removeAttribute("hidden", "");
                    sButton.removeAttribute("hidden", "");
                    title.setAttribute(
                        "datetime",
                        formatDate(date, formats.datetime)
                    );
                }
                if (view === "month" && type === "date") {
                    footer.removeAttribute("hidden", "");
                    aButton.removeAttribute("hidden", "");
                    aButton.innerHTML = i18n.today;
                }
                if (view === "year") {
                    p = date.getFullYear() - 1;
                    pDate = new Date(p, 0, 0);
                    n = date.getFullYear() + 1;
                    nDate = new Date(n, 0, 0);
                    label.innerHTML = date.getFullYear();
                }
                if (view === "decade") {
                    p = yearSpan(date.getFullYear(), 10)[0] - 1;
                    pDate = new Date(p, 0, 0);
                    n = yearSpan(date.getFullYear(), 10)[1] + 1;
                    nDate = new Date(n, 0, 0);
                    label.innerHTML = p + 1 + " - " + (n - 1);
                    if (mode.match(/(multiple|range)/gi)) {
                        footer.removeAttribute("hidden", "");
                        aButton.removeAttribute("hidden", "");
                    }
                }
                if (view === "time") {
                    if (type === "time") {
                        pButton.setAttribute("hidden", "");
                        label.innerHTML = i18n.settime;
                    } else
                        label.innerHTML = formatDate(
                            date,
                            i18n.longformat
                        );
                    nButton.firstChild.innerHTML = icons.close;
                    nButton.setAttribute("aria-label", i18n.close);
                    footer.removeAttribute("hidden", "");
                    aButton.removeAttribute("hidden", "");
                }
                if (view === "settings") {
                    nButton.firstChild.innerHTML = icons.close;
                    nButton.setAttribute("aria-label", i18n.close);
                    label.innerHTML = i18n.settings;
                }
                // Reset event listeners
                aButton.onclick = set;
                pButton.onclick = previous;
                nButton.onclick = next;
                sButton.onclick = configure;
                label.onclick = index;
                // Define future / past dates
                var isPast = nDate.getTime() < today.getTime(),
                    isFuture = pDate.getTime() > today.getTime();
                // Set and display view
                current = {
                    name: view,
                    el: el,
                    items: el.querySelectorAll("a"),
                };
                display();
                // Methods
                function display() {
                    var vs = picker.querySelectorAll(".show");
                    [].slice.call(vs).forEach(function(v) {
                        v.className = v.className
                            .replace(/\bshow\b/g, "")
                            .trim();
                        v.setAttribute("hidden", "");
                    });
                    el.removeAttribute("hidden");
                    setTimeout(function() {
                        el.className = (el.className + " show").trim();
                    }, 2);
                    if (!view.match(/(time|settings|day)/gi))
                        setTimeout(function() {
                            active
                                ?
                                active.focus() :
                                current.items[0].focus();
                        }, 300);
                }

                function previous(e) {
                    if (options.disablePast && isPast) return false;
                    if (view === "time") monthView();
                    else if (view === "year") yearView(p);
                    else if (view === "decade") decadeView(p);
                    else if (view === "settings") monthView();
                    else monthView(p);
                }

                function next(e) {
                    if (options.disableFuture && isFuture) return false;
                    if (type === "time") toggle();
                    else if (view === "year") yearView(n);
                    else if (view === "decade") decadeView(n);
                    else if (view === "time") monthView();
                    else monthView(n);
                }

                function index(e) {
                    if (view === "decade" || type === "time")
                        return false;
                    else if (view === "year") decadeView();
                    else if (view === "time") monthView();
                    else yearView();
                }

                function set(e) {
                    var val;
                    switch (type) {
                        case "time":
                            val = formatDate(date, formats.time);
                            break;
                        case "datetime":
                            val = formatDate(date, formats.datetime)
                                .trim()
                                .replace(" ", "T");
                            break;
                        case "month":
                            val = formatDate(date, formats.month);
                            break;
                        case "year":
                            val = date.getFullYear();
                            break;
                        default:
                            val = formatDate(date, formats.date);
                    }
                    trigger.value = val;
                    toggle();
                }

                function configure(e) {
                    settingsView();
                }
            }
            // Views
            function clockView(hrs, min) {
                // to do
                view = "clock";
                if (hrs) date.setHours(hrs);
                if (min) date.setMinutes(min);
                createTime();
                setView();
            }

            function timeView(hrs, min) {
                view = "time";
                if (hrs) date.setHours(hrs);
                if (min) date.setMinutes(min);
                createTime();
                setView();
            }

            function dayView(num) {
                view = "day";
                if (num)
                    date.setDate(
                        date.getFullYear(),
                        date.getMonth(),
                        num
                    );
                setView();
            }

            function monthView(num) {
                view = "month";
                if (num) date.setMonth(num);
                createMonth();
                setView();
            }

            function yearView(num) {
                view = "year";
                if (num) date.setFullYear(num);
                createYear();
                setView();
            }

            function decadeView(num) {
                view = "decade";
                if (num) date.setFullYear(num);
                createYears(10);
                setView();
            }

            function settingsView() {
                current.previousView = view;
                view = "settings";
                createSettings();
                setView();
            }
            // Keyboard events
            function keyup(e) {
                var fl = false,
                    s = view === "month" ? 7 : 3,
                    el =
                    current.el.querySelector(
                        ":not([disabled]):focus, [aria-selected]:not([disabled])"
                    ) || current.items[0],
                    index = [].indexOf.call(current.items, el);
                switch (e.keyCode) {
                    case keys.esc:
                        toggle(e);
                        break;
                    case keys.space:
                        if (el) el.click();
                        break;
                    case keys.right:
                        el && el.nextSibling ?
                            el.nextSibling.focus() :
                            toNext;
                        fl = true;
                        break;
                    case keys.left:
                        el && el.previousSibling ?
                            el.previousSibling.focus() :
                            toPrevious;
                        fl = true;
                        break;
                    case keys.down:
                        typeof current.items[index + s] !== "undefined" ?
                            current.items[index + s].focus() :
                            toNext;
                        fl = true;
                        break;
                    case keys.up:
                        typeof current.items[index - s] !== "undefined" ?
                            current.items[index - s].focus() :
                            toPrevious;
                        fl = true;
                        break;
                    case keys.pgup:
                        toPrevious();
                        fl = true;
                        break;
                    case keys.pgdown:
                        toNext();
                        fl = true;
                        break;
                    case keys.home:
                        current.items[0].focus();
                        fl = true;
                        break;
                    case keys.end:
                        current.items[current.items.length - 1].focus();
                        fl = true;
                        break;
                }
                if (fl) prevent;
                // Methods
                function toPrevious() {
                    pButton.click();
                }

                function toNext() {
                    nButton.click();
                }
            }
        }
        // Helper
        function prevent(e) {
            e.stopPropagation();
            e.preventDefault();
        }

        function mergeObj(o1, o2) {
            for (var key in o2) o1[key] = o2[key];
            return o1;
        }

        function mergeArr(a1, a2) {
            return a1.concat(
                a2.filter(function(m) {
                    return a1.indexOf(m) === -1;
                })
            );
        }

        function mergeArrObj(a1, a2) {
            return a1.map(function(item, i) {
                if (a2[i] && item.id === a2[i].id) return a2[i];
                else return item;
            });
        }

        function arrAddValue(a, v) {
            a.push(v);
            return a.filter(function(val, i, s) {
                return s.indexOf(val) === i;
            });
        }

        function ucFirst(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }

        function yearSpan(number, interval) {
            var s = Math.floor(number / interval) * interval;
            return [s, s + interval - 1];
        }

        function formatDate(date, format) {
            var z = {
                M: date.getMonth() + 1,
                d: date.getDate(),
                h: date.getHours(),
                m: date.getMinutes(),
                s: date.getSeconds(),
            };
            format = format.replace(
                /\b(M{1,2}|d{1,2}|h{1,2}|m{1,2}|s{1,2})\b/g,
                function(v) {
                    return (
                        (v.length > 1 ? "0" : "") + z[v.slice(-1)]
                    ).slice(-2);
                }
            );
            format = format.replace(/(y+)/g, function(v) {
                return date.getFullYear().toString().slice(-v.length);
            });
            format = format.replace(
                /(D|ddd+)/g,
                i18n.days[date.getDay() - 1]
            );
            format = format.replace(
                /(MMM+)/g,
                i18n.months[date.getMonth()]
            );
            return format;
        }
    }
</script>
