(function ($) {
  $(document).ready(function () {
    /* Add search parameter to form */
    var query = window.location.search,
      params = new URLSearchParams(query);

    if (params.has("search"))
      $('.search-form input[type="search"]').val(params.get("search"));
  });

  /* Search helper */
  $(document).on("submit", ".search-form", function (e) {
    e.preventDefault();

    if (window.location.pathname !== "/resources/")
      window.location =
        "/resources/?search=" +
        encodeURIComponent($(this).find('input[type="search"]').val());
    else adjustFilters($(this));

    console.log("hit");
  });

  /* Add filters helper */
  $(document).on("change", ".filter-input input", function () {
    adjustFilters($(this));
  });

  /* Filter clear all helper tool */
  $(document).on("click", ".clear-all", function () {
    if (!$(this).hasClass("main")) {
      $(this).parent().find("input").prop("checked", false).trigger("change");
    } else {
      // Clear all filters
      $(".filter-input input:checked").prop("checked", false).trigger("change");
      $('input[id*="filter"]:checked').prop("checked", false).trigger("change");

      // Clear the search field
      $('input[name="s"]').val("").trigger("change"); // Reset search input field

      // Remove the search query from the URL without reloading the page
      var url = window.location.href.split("?")[0]; // Get the URL without query params
      window.history.pushState({}, document.title, url); // Replace the URL in history

      // Trigger the SearchWP Ajax search form refresh
      $("form.search-form-side").submit(); // Submit the form via Ajax
    }
  });

  // Detect when the search query is cleared
  $('input[name="s"]').on("input", function () {
    // If the search input is cleared (empty), submit the form to update results
    if ($(this).val() === "") {
      // Trigger the SearchWP Ajax search form refresh
      $("form.search-form-side").submit();

      // Optionally, remove the search query from the URL
      var url = window.location.href.split("?")[0];
      window.history.pushState({}, document.title, url);
    }
  });

  /* Copy link helper */
  $(document).on("click", ".share.link", function (e) {
    e.preventDefault();

    $(this).addClass("copied");
    navigator.clipboard.writeText(window.location.href);
  });

  /* Update sort records label */
  $(document).on("change", "#sort-records", function () {
    adjustFilters($(this));
  });

  /* Trigger load more records */
  $(document).on("click", "#load-more-records", function (e) {
    e.preventDefault();

    adjustFilters($(this));
  });

  function adjustFilters(e) {
    var records = parseInt($(".records .async__inner").attr("data-current")),
      sections = $(".filter-section"),
      search = searchParams("search"),
      filters = "",
      sorted = "",
      queryString = "";

    if (search == "") {
      search = $('input[type="search"].search-field-side').val();
    }

    /* Select all helper */
    if (e.attr("id") && e.attr("id").indexOf("all") !== -1) {
      if (e.prop("checked") === true)
        e.parent().parent().find("input").prop("checked", true);
      else e.parent().parent().find("input").prop("checked", false);
    }

    /* Load more records helper */
    if (e.attr("id") && e.attr("id") === "load-more-records")
      records = records + 8;
    else records = 8;

    /* Build query string */
    if (e.attr("id") && e.attr("id") === "sort-records") sorted = e.val();
    else sorted = $("#resources-results #sort-records").val();

    queryString = "?records=" + records + "&sort=" + sorted;

    sections.each(function () {
      var params,
        taxonomy = $(this).attr("data-taxonomy"),
        terms = $(this)
          .find(".filter-input input:checked")
          .map(function () {
            return this.value;
          })
          .get();

      params = terms.join(",");

      if (params !== "")
        queryString += "&" + taxonomy + "=" + encodeURIComponent(params);
    });

    /* Check status of search parameter */
    if (e.attr("class").indexOf("search-form") !== -1) {
      queryString +=
        "&search=" +
        encodeURIComponent(
          e.find('input[type="search"].search-field-side').val()
        );
    } else if (search) {
      if (e.attr("id")) {
        if (
          e.attr("id") === "sort-records" ||
          e.attr("id") === "load-more-records" ||
          e.attr("type") == "checkbox"
        ) {
          queryString += "&search=" + encodeURIComponent(search);
        }
      }
    }

    console.log(queryString);

    /* Display active filters */
    $(".filter-input input")
      .each(function () {
        if (e.attr("id") && $(this).attr("id").indexOf("all") === -1) {
          if ($(this).prop("checked") === true) {
            var filterName = $(this).next().text(),
              filterTrigger = $(this).attr("id");

            if (filters === "") filters = '<div class="filters__inner">';

            filters +=
              '<label for="' +
              filterTrigger +
              '" class="filters__inner__filter">' +
              filterName +
              "</label>";
          } else {
            $(this)
              .parent()
              .parent()
              .find('input[id*="all"]')
              .prop("checked", false);
          }
        }
      })
      .promise()
      .done(function () {
        $(".filters").html(filters + "</div>");
      })
      .promise()
      .done(function () {
        $("#records").addClass("loading");
        $("#records").load(
          "/resources/" + queryString + " .async",
          function () {
            var url =
              window.location.protocol +
              "//" +
              window.location.host +
              window.location.pathname +
              queryString;

            window.history.pushState(
              {
                path: url,
              },
              "",
              url
            );

            $(".records .async__inner").attr("data-current", records);
            $("#records").removeClass("loading");
          }
        );
      });
  }

  /* Build search parameters */
  function searchParams(term) {
    term = term.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");

    var regex = new RegExp("[\\?&]" + term + "=([^&#]*)"),
      results = regex.exec(location.search);

    return results === null
      ? null
      : decodeURIComponent(results[1].replace(/\+/g, " "));
  }
})(jQuery);
