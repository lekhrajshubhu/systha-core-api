<style>
    /* /* @import url(https://fonts.googleapis.com/css?family=Montserrat:200,400,700); */



  
</style>
<div id="custom_datetime">
    <div class="mb-5 d-flex align-items-center">
        <button type="button" class="btn btn-info circle me-3"
        id="btnBackSchedule"
        style="height: 40px; width:40px">
            <i class="fa-solid fa-arrow-left"></i>
        </button>

        <h4 style="font-weight: 800" class="pl-2">Date & Time</h4>
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


        var tomorrow = new Date();
        // tomorrow.setDate(tomorrow.getDate() + 1); // move to next day
        tomorrow.setDate(tomorrow.getDate()); // move to next day
        var yyyy = tomorrow.getFullYear();
        var mm = String(tomorrow.getMonth() + 1).padStart(2, '0'); // months are 0-indexed
        var dd = String(tomorrow.getDate()).padStart(2, '0');
        var todayStr = `${yyyy}-${mm}-${dd}`; // → "YYYY-MM-DD" format
        var pickerOptions = {
            disablePast: true,
            disableFuture: true,
            disableWeekend: false,
            useClock: false,
            weekStart: 1,
            minStep: 15,
            hrsStep: 1,
            enabledDates: [], // dates must match formatDate output   ['2025-07-16', '2025-07-18', '2025-07-25'],
            minDate: todayStr, // example ISO date string, set your desired min date here - '2025-07-10'
            maxDate: '', // example ISO date string, set your desired max date here - '2025-07-25'
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
            function createPicker() {
                picker = d.createElement("div");
                picker.id = id;
                picker.setAttribute("role", "modal");
                picker.setAttribute("aria-modal", true);
                picker.setAttribute("aria-labelledby", id + "-label");
                picker.setAttribute("tabindex", 0);
                picker.className = "datepicker";
                picker.innerHTML = tpl;
                // Assign ui layout & controls
                indicator = picker.querySelector(".indicator");
                header = picker.querySelector(".datepicker-header");
                footer = picker.querySelector(".datepicker-footer");
                navigation = picker.querySelector(
                    ".datepicker-navigation"
                );
                title = header.querySelector("time");
                time = picker.querySelector("form.time");
                minutes = time.querySelector(".minutes");
                hours = time.querySelector(".hours");
                day = picker.querySelector(".day");
                month = picker.querySelector(".month");
                week = month.querySelector(".week");
                days = month.querySelector(".days");
                year = picker.querySelector(".year");
                decade = picker.querySelector(".decade");
                settings = picker.querySelector(".settings");
                (label = navigation.querySelector("h3")),
                (nButton = navigation.querySelector(".next")),
                (pButton = navigation.querySelector(".prev")),
                (aButton = footer.querySelector(":first-child")),
                (sButton = header.querySelector("button")),
                // Set unique ids, inject & call view
                (label.id = id + "-label");
                trigger.parentElement.appendChild(picker);
                picker.oncontextmenu = prevent;
                picker.addEventListener("keyup", keyup, false);
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
                const el = d.createElement("a");
                const ti = d.createElement("time");
                const string = formatDate(date, formats.date); // e.g. "2025-07-15"
                const isToday = date.toString() === today.toString();
                const dayEvents = hasEvents(string);

                // ▶️ Basic markup
                ti.innerHTML = num;
                el.href = "#" + string;
                ti.title = formatDate(date, i18n.displayformat);
                ti.setAttribute("datetime", string);

                // Margin for the first day of the month
                if (num === 1) {
                    el.style.marginLeft = (dd === 0 ? 6 : dd - 1) * 14.28 + "%";
                }

                /* ──────────────────────────────────
                    ENABLE / DISABLE logic begins
                   ────────────────────────────────── */
                const hasEnabledDates =
                    Array.isArray(options.enabledDates) && options.enabledDates.length > 0;

                if (hasEnabledDates) {
                    /* 1️⃣  enabledDates takes priority when non‑empty  */
                    if (options.enabledDates.includes(string)) {
                        enable(el);
                    } else {
                        disable(el);
                    }
                } else {
                    /* 2️⃣  fall back to min/max + other rules          */
                    const minDate = options.minDate ? new Date(options.minDate) : null;
                    const maxDate = options.maxDate ? new Date(options.maxDate) : null;

                    const beforeMin = minDate && date < minDate;
                    const afterMax = maxDate && date > maxDate;

                    if (beforeMin || afterMax) {
                        disable(el);
                    } else if (options.disablePast && date <= today) {
                        disable(el);
                    } else if (options.disableWeekend && (dd === 0 || dd === 6)) {
                        disable(el);
                    } else {
                        enable(el);
                    }
                }
                /* ────────────────────────────────── */

                // Highlight today
                if (isToday) {
                    el.className += " today";
                    ti.title = ucFirst(i18n.today);
                    ti.setAttribute("aria-current", "date");
                }

                // Append <time> element
                el.appendChild(ti);

                // Events tooltip
                if (dayEvents && dayEvents.length) {
                    const tip = d.createElement("span");
                    dayEvents.forEach(evtObj => {
                        const evt = d.createElement("i");
                        evt.innerHTML = ucFirst(evtObj.name);
                        evt.setAttribute("datetime", evtObj.date);
                        evt.setAttribute("data-type", evtObj.type || "holiday");
                        ti.title += "\n" + evt.title;
                        tip.appendChild(evt);
                    });
                    el.appendChild(tip);
                }

                // Selection state
                if (isSelected(string)) el.setAttribute("aria-selected", true);

                // Add to calendar
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
