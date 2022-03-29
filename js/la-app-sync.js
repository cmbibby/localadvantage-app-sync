document.addEventListener("click", (event) => {
	var infoContainer = document.getElementById("infoContainer");
	var updateTime = document.getElementById("updateTime");

	if (event.target.matches("#import_all")) {
		var spinner = document.getElementById("allOffersSpinner");
		infoContainer.textContent = "Fetching all Offers from API...";
		spinner.classList.add("is-active");
		wp.ajax
			.post("get_offers_all", {})
			.done((response) => {
				infoContainer.textContent =
					"Successfully processed " +
					response.offer_count +
					" offers.";
				updateTime.textContent = response.last_updated_at;
				spinner.classList.remove("is-active");
			})
			.fail((err) => {
				infoContainer.textContent = "The Shit hit the fan";
				spinner.classList.remove("is-active");
				console.log(err);
			});
	}
	if (event.target.matches("#import_latest")) {
		var spinner = document.getElementById("latestOffersSpinner");
		infoContainer.textContent = "Fetching latest Offers from API...";
		spinner.classList.add("is-active");
		wp.ajax
			.post("get_offers_latest", {})
			.done((response) => {
				infoContainer.textContent =
					"Successfully processed " +
					response.offer_count +
					" offers.";
				updateTime.textContent = response.last_updated_at;
				spinner.classList.remove("is-active");
			})
			.fail((err) => {
				infoContainer.textContent = "The Shit hit the fan";
				spinner.classList.remove("is-active");
				console.log(err);
			});
	}
});
