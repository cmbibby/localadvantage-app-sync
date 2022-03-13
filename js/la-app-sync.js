document.addEventListener("click", (event) => {
	if (!event.target.matches("#import_all")) return;
	var infoContainer = document.getElementById("infoContainer");
	var updateTime = document.getElementById("updateTime");
	var spinner = document.getElementById("allOffersSpinner");
	infoContainer.textContent = "Fetching Offers from API...";
	spinner.classList.add("is-active");
	wp.ajax
		.post("get_offers_from_api", {})
		.done((response) => {
			infoContainer.textContent =
				"Successfully imported " + response.offer_count + " offers.";
			updateTime.textContent = response.last_updated_at;
			spinner.classList.remove("is-active");
		})
		.fail((err) => {
			alert("something went down");
			infoContainer.textContent = "The Shit hit the fan";
			console.log(err);
		});
});
