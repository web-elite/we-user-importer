(function ($) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	document.addEventListener('DOMContentLoaded', function () {
		const btn = document.getElementById('downloadCsv');
		if (btn) {
			btn.addEventListener('click', function (e) {
				e.preventDefault();

				const rows = [
					[
						"شماره موبایل (الزامی)",
						"مبلغ خرید قبلی (به تومان )",
						"مبلغ شارژ (تومان)",
						"نام",
						"نام خانوادگی",
						"استان",
						"شهر",
					]
				];

				const csvContent = '\uFEFF' + rows.map(e => e.join(",")).join("\n");

				const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
				const url = URL.createObjectURL(blob);

				const link = document.createElement("a");
				link.setAttribute("href", url);
				link.setAttribute("download", "sample.csv");
				link.click();

				URL.revokeObjectURL(url);
			})
		}
	});

})(jQuery);
