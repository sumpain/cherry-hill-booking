jQuery(document).ready(function (i) {
    var e, t, a, n, o;
    i("#ch-booking-form").length &&
        ("yes" == i("#form-sent").val() && window.location.reload(),
        (e = new Date()),
        (t = new Date()),
        new Date(),
        (a = postCodes.getCouncil(i("#postcode-select").val())),
        t.setDate(e.getDate() + 3),
        t.setDate(e.getDate() + checkWeekEnd(t)),
        (n = i("#hire-period-input-from")
            .datepicker({ minDate: "+3d", defaultDate: "+3d", dateFormat: "yy-mm-dd", beforeShowDay: noWeekendsOrHolidays })
            .datepicker("setDate", new Date())
            .on("change", function () {
                o.datepicker("option", "minDate", n.datepicker("getDate").addDays(5)), o.datepicker("option", "maxDate", n.datepicker("getDate").addDays(14));
            })),
        (o = i("#hire-period-input-until")
            .datepicker({ minDate: n.datepicker("getDate").addDays(5), maxDate: "+2w", defaultDate: "+1w", dateFormat: "yy-mm-dd", beforeShowDay: noWeekendsOrHolidays })
            .datepicker("setDate", "+1w")
            .on("change", function () {})),
        calculatePrice(i),
        checkSkips(i),
        i(".chb-type-tabs")
            .find("span")
            .on("click", function (e) {
                var t = i(this);
                t.addClass("chb-tab-selected"), t.siblings("span").removeClass("chb-tab-selected"), i("#skip-type-select").val(t.data("value")).trigger("change");
            }),
        i("#postcode-select").on("change", function (e) {
            checkSkips(i), checkParkingPermit(i);
        }),
        i("input[type=radio][name=waste-type]").on("change", function (e) {
            checkSkips(i);
        }),
        i("input[type=radio][name=land-type]").on("change", function (e) {
            checkParkingPermit(i);
            var t = n.datepicker("getDate");
            (addedBusinessDays = addBusinessDays(t, a.noticePeriod)), console.log(addedBusinessDays), t.addDays(addedBusinessDays), n.datepicker("option", "minDate", t), n.trigger("change");
        }),
        i(".calendar-icon").click(function () {
            var e = "#" + i(this).data("for");
            i(e).datepicker("show");
        }),
        i("#ch-booking-form")
            .find("input, select")
            .on("change", function (e) {
                calculatePrice(i);
            }),
        i("#ch-book-now").on("click", function (e) {
            e.preventDefault();
            var ps = i("#postcode-select").val(),
		        sss = i("#skip-size-select").val(),
		        ltp = i("#land-type-public").is(":checked"),
		        tp = getPrice(ps, sss, ltp, ltp && i("#parking-permit-required").is(":checked"), Math.round(Math.abs(new Date(i("#hire-period-input-from").val()).getTime() - new Date(i("#hire-period-input-until").val()).getTime()) / 864e5));

            if(i("#hire-period-input-from").val() == 'Select Start Date' || i("#hire-period-input-from").val() == ''){
                i( "#hire-period-input-from" ).focus();
                return false;
            }
            if(i("#hire-period-input-until").val() == 'Select End Date' || i("#hire-period-input-until").val() == ''){
                i( "#hire-period-input-until" ).focus();
                return false;
            }
            var t = {
                purpose: i(".chb-type-tabs").find(".chb-tab-selected").html(),
                size: i("#skip-size-select option:selected").text(),
                postcode: i("#postcode-select").val(),
                period: "From " + i("#hire-period-input-from").val() + " Until " + i("#hire-period-input-until").val(),
                wasteType: i("label[for=" + i("input[name=waste-type]:checked", "#ch-booking-form").attr("id") + "]").text(),
                landType: i("label[for=" + i("input[name=land-type]:checked", "#ch-booking-form").attr("id") + "]").text(),
                parkingPermit: i("label[for=" + i("input[name=parking-permit]:checked", "#ch-booking-form").attr("id") + "]").text(),
                price: tp,
            };
            i.ajax({
                method: "post",
                url: ajax_ch_booking_object.ajaxurl,
                data: { action: "ch_booking_handler", product: t },
                success: function (e) {
                    i("#form-sent").attr("value", "yes"), (window.location.href = e);
                },
                error: function (e) {
                    console.log(e);
                },
            });
        }));
});
var addedBusinessDays = 0;
function checkWeekEnd(e) {
    switch (e.getDay()) {
        case 0:
            return 4;
        case 6:
        default:
            return 3;
    }
}
function addBusinessDays(e, t) {
    for (var i = 0; 0 < t; ) e.setDate(e.getDate() + 1), i++, noWeekendsOrHolidays(e)[0] && t--;
    return i;
}
function noWeekendsOrHolidays(e) {
    jQuery.datepicker.noWeekends(e);
    return 0 != e.getDay() && nationalDays(e);
}
function nationalDays(e) {
    var t = [
        [1, 1, "uk"],
        [3, 18, "uk"],
        [4, 19, "uk"],
        [4, 22, "uk"],
        [5, 6, "uk"],
        [5, 27, "uk"],
        [7, 12, "uk"],
        [8, 5, "uk"],
        [8, 26, "uk"],
        [12, 2, "uk"],
        [12, 25, "uk"],
        [12, 26, "uk"],
    ];
    for (i = 0; i < t.length; i++) if (e.getMonth() == t[i][0] - 1 && e.getDate() == t[i][1]) return [!1, t[i][2] + "_day"];
    return [!0, ""];
}
function checkSkips(e) {
	//var t = { 12: "12yrd", "12e": "12yrd enclosed" },
    var t = { 12: "12yrd" },
        i = e("#postcode-select").val(),
        a = e("#waste-type-bs").is(":checked"),
        n = e("#skip-size-select");
    if ("ST1" == i && a) for (var o in t) n.find('option[value="' + o + '"]').remove();
    else for (var o in t) n.find('option[value="' + o + '"]').length || n.append(e('<option value="' + o + '">' + t[o] + "</option>"));
}
Date.prototype.addDays = function (e) {
    var t = new Date(this.valueOf());
    return t.setDate(t.getDate() + e), t;
};
var postCodes = {
    councils: {
        stoke: { codes: ["ST1", "ST2", "ST3", "ST4", "ST6"], price: 40, noticePeriod: 3, validPeriod: 28, parking: { price: 15, period: 7 } },
        staffordshire: { codes: ["ST5", "ST7", "ST8", "ST9", "ST10", "ST11", "ST12", "ST13", "ST14", "ST15", "ST16", "ST17", "ST18", "ST20", "ST21"], price: 36, noticePeriod: 5, validPeriod: 7 },
        cheshire: { codes: ["CW1", "CW2", "CW5", "CW11", "CW12"], price: 75, noticePeriod: 2, validPeriod: 28 },
        other: { codes: ["TF9"], price: 120, noticePeriod: 7, validPeriod: 0 },
    },
    getCouncil: function (e) {
        var t = Object.keys(this.councils),
            i = !1;
        for (key in t) -1 < this.councils[t[key]].codes.indexOf(e) && (i = this.councils[t[key]]);
        return i;
    },
};
function checkParkingPermit(e) {
    var t = e("#postcode-select").val(),
        i = e("#land-type-public").is(":checked");
    postCodes.getCouncil(t).parking && i ? e(".parking-permit-wrapper").slideDown() : (e("#parking-permit-none").prop("checked", !0), e(".parking-permit-wrapper").slideUp());
}
function calculatePrice(e) {
    var t = e("#postcode-select").val(),
        i = e("#skip-size-select").val(),
        a = e("#land-type-public").is(":checked"),
        n = getPrice(t, i, a, a && e("#parking-permit-required").is(":checked"), Math.round(Math.abs(new Date(e("#hire-period-input-from").val()).getTime() - new Date(e("#hire-period-input-until").val()).getTime()) / 864e5));
    e("#quoted-price").find("span").html(n);
}
function getPrice(e, t, i, a, n) {	
    var o,
        c = postCodes.getCouncil(e),
        r = 0;
    a && c.parking && ((0 != (o = Math.ceil(n / c.parking.period)) && !isNaN(o)) || (o = 1), (r = c.parking.price * o));
    var d,
        s,
        p = 0;
    return (
        i && ((s = 0 == (d = n / c.validPeriod) || isNaN(s) ? (s = 1) : Math.ceil(d)), (p = c.price * s)),
        (0 != (n = Math.ceil(n / 14)) && !isNaN(n)) || (n = 1),
        {
            ST1: [90, 108, 120, 144, 180, 216, 252, 240, 40],
            ST2: [90, 108, 120, 144, 180, 216, 252, 240, 40],
            ST3: [90, 108, 120, 144, 180, 216, 252, 240, 40],
            ST4: [90, 108, 120, 144, 180, 216, 252, 240, 40],
            ST5: [90, 108, 120, 144, 180, 216, 252, 240, 35],
            ST6: [90, 108, 120, 144, 180, 216, 252,240, 40],
            ST7: [90, 108, 120, 144, 180, 216, 252, 240, 35],
            ST8: [96, 114, 126, 150, 186, 222, 258, 246, 40],
            ST9: [96, 114, 126, 150, 186, 222, 258, 246, 40],
            ST10: [114, 132, 144, 168, 210, 246, 258, 40],
            ST11: [108, 126, 138, 168, 204, 240, 258, 40],
            ST12: [96, 114, 126, 150, 186, 222, 240, 40],
            ST13: [114, 132, 144, 168, 204, 246, 264, 35],
            ST14: [126, 144, 156, 180, 216, 240, 300, 280, 35],
            ST15: [102, 120, 132, 156, 198, 234, 282, 252, 35],
            ST16: [120, 138, 150, 174, 210, 252, 294, 265, 35],
            ST17: [126, 144, 156, 180, 216, 258, 294, 280, 35],
            ST18: [132, 150, 162, 186, 222, 264, 300, 288, 35],
            ST20: [132, 150, 162, 186, 222, 264, 300, 300, 35],
            ST21: [120, 138, 150, 174, 210, 252, 288, 258, 35],
            TF9: [120, 138, 150, 174, 216, 252, 288, 268, 35],
            CW1: [102, 120, 132, 156, 192, 234, 270, 252, 75],
            CW2: [102, 120, 132, 156, 192, 234, 270, 252, 75],
            CW3: [90, 108, 120, 144, 180, 204, 240, 240, 75],
            CW5: [108, 126, 138, 168, 198, 240, 276, 264, 75],
            CW11: [108, 126, 138, 168, 204, 240, 276, 264, 75],
            CW12: [114, 132, 144, 168, 204, 246, 282, 264, 75],
        }[e][["2", "3", "4", "5", "8", "10", "12", "12e", "permit"].indexOf(t)] *
            n +
            r +
            p
    );
}