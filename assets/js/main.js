import GLightbox from "glightbox";
import AOS from "aos";
import Isotope from "isotope-layout";
import L from "leaflet";
import markerIcon2x from "leaflet/dist/images/marker-marrymix-icon-2x.svg";
import markerIcon from "leaflet/dist/images/marker-marrymix-icon.svg";
import markerShadow from "leaflet/dist/images/marker-shadow.png";
import Datepicker from "flowbite-datepicker/Datepicker";
import Swiper from "swiper/bundle";

(function () {
  "use strict";

  /**
   * Easy selector helper function
   */
  const select = (el, all = false) => {
    el = el.trim();
    if (all) {
      return [...document.querySelectorAll(el)];
    } else {
      return document.querySelector(el);
    }
  };

  /**
   * Easy event listener function
   */
  const on = (type, el, listener, all = false) => {
    let selectEl = select(el, all);
    if (selectEl) {
      if (all) {
        selectEl.forEach((e) => e.addEventListener(type, listener));
      } else {
        selectEl.addEventListener(type, listener);
      }
    }
  };

  /**
   * Easy on scroll event listener
   */
  const onscroll = (el, listener) => {
    el.addEventListener("scroll", listener);
  };

  /**
   * Navbar links active state on scroll
   */
  let navbarlinks = select("#navbar .scrollto", true);
  const navbarlinksActive = () => {
    let position = window.scrollY + 200;
    navbarlinks.forEach((navbarlink) => {
      if (!navbarlink.hash) return;
      let section = select(navbarlink.hash);
      if (!section) return;
      if (
        position >= section.offsetTop &&
        position <= section.offsetTop + section.offsetHeight
      ) {
        navbarlink.classList.add("active");
      } else {
        navbarlink.classList.remove("active");
      }
    });
  };
  window.addEventListener("load", navbarlinksActive);
  onscroll(document, navbarlinksActive);

  /**
   * Scrolls to an element with header offset
   */
  const scrollto = (el) => {
    let header = select("#header");
    let offset = header.offsetHeight;

    let elementPos = select(el).offsetTop;
    window.scrollTo({
      top: elementPos - offset,
      behavior: "smooth",
    });
  };

  /**
   * Toggle .header-scrolled class to #header when page is scrolled
   */
  let selectHeader = select("#header");
  let selectTopbar = select("#topbar");
  if (selectHeader) {
    const headerScrolled = () => {
      if (window.scrollY > 100) {
        selectHeader.classList.add("header-scrolled");
        if (selectTopbar) {
          selectTopbar.classList.add("topbar-scrolled");
        }
      } else {
        selectHeader.classList.remove("header-scrolled");
        if (selectTopbar) {
          selectTopbar.classList.remove("topbar-scrolled");
        }
      }
    };
    window.addEventListener("load", headerScrolled);
    onscroll(document, headerScrolled);
  }

  let selectMain = select("#main");
  if (selectMain && selectHeader) {
    const setMainOffset = () => {
      let mainOffset = selectHeader.offsetHeight + 40;
      selectMain.style.marginTop = mainOffset + "px";
    };
    window.addEventListener("load", setMainOffset);
    window.addEventListener("resize", setMainOffset);
  }

  /**
   * Back to top button
   */
  let backtotop = select(".back-to-top");
  if (backtotop) {
    const toggleBacktotop = () => {
      if (window.scrollY > 100) {
        backtotop.classList.add("active");
      } else {
        backtotop.classList.remove("active");
      }
    };
    window.addEventListener("load", toggleBacktotop);
    onscroll(document, toggleBacktotop);
  }

  /**
   * Mobile nav toggle
   */
  on("click", ".mobile-nav-toggle", function (e) {
    select("#navbar").classList.toggle("navbar-mobile");
    this.classList.toggle("bi-list");
    this.classList.toggle("bi-x");
  });

  /**
   * Mobile nav dropdowns activate
   */
  on(
    "click",
    ".navbar .dropdown > a",
    function (e) {
      if (select("#navbar").classList.contains("navbar-mobile")) {
        e.preventDefault();
        this.nextElementSibling.classList.toggle("dropdown-active");
      }
    },
    true
  );

  /**
   * Scrool with ofset on links with a class name .scrollto
   */
  on(
    "click",
    ".scrollto",
    function (e) {
      if (select(this.hash)) {
        e.preventDefault();

        let navbar = select("#navbar");
        if (navbar.classList.contains("navbar-mobile")) {
          navbar.classList.remove("navbar-mobile");
          let navbarToggle = select(".mobile-nav-toggle");
          navbarToggle.classList.toggle("bi-list");
          navbarToggle.classList.toggle("bi-x");
        }
        scrollto(this.hash);
      }
    },
    true
  );

  /**
   * Scroll with ofset on page load with hash links in the url
   */
  window.addEventListener("load", () => {
    if (window.location.hash) {
      if (select(window.location.hash)) {
        scrollto(window.location.hash);
      }
    }
  });

  /**
   * Preloader
   */
  let preloader = select("#preloader");
  if (preloader) {
    window.addEventListener("load", () => {
      preloader.remove();
    });
  }

  /**
   * Menu isotope and filter
   */
  window.addEventListener("load", () => {
    let menuContainer = select(".menu-container");
    if (menuContainer) {
      let menuIsotope = new Isotope(menuContainer, {
        itemSelector: ".menu-item",
        layoutMode: "fitRows",
      });

      let menuFilters = select("#menu-flters li", true);

      on(
        "click",
        "#menu-flters li",
        function (e) {
          e.preventDefault();
          menuFilters.forEach(function (el) {
            el.classList.remove("filter-active");
          });
          this.classList.add("filter-active");

          menuIsotope.arrange({
            filter: this.getAttribute("data-filter"),
          });
          menuIsotope.on("arrangeComplete", function () {
            AOS.refresh();
          });
        },
        true
      );
    }
  });

  /**
   * Swiper
   */
  const swiper = new Swiper(".swiper", {
    // Optional parameters
    direction: "vertical",
    loop: true,
    speed: 600,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false,
    },
    slidesPerView: "auto",
    pagination: {
      el: ".swiper-pagination",
      type: "bullets",
      clickable: true,
    },
  });

  /**
   * Initiate glightbox
   */
  const glightbox = GLightbox({
    selector: ".glightbox",
  });

  /**
   * Animation on scroll
   */
  window.addEventListener("load", () => {
    AOS.init({
      duration: 1000,
      easing: "ease-in-out",
      once: true,
      mirror: false,
    });
  });

  /**
   * AJAX for Add To Cart - MENU
   */
  const handleAddToCart = () => {
    const productLinks = document.querySelectorAll(".btn-add-to-cart");
    let glightboxes = [];
    for (let i = 1; i <= productLinks.length; i++) {
      glightboxes[i] = GLightbox({
        selector: ".glightbox" + i,
      });
    }

    productLinks.forEach(function (link) {
      link.addEventListener("click", function (event) {
        event.preventDefault();

        fetch(link.href)
          .then((response) => response.json())
          .then((json) => {
            if (json.code == "ITEM_ADDED_SUCCESSFULLY") {
              link.classList.add("pointer-events-none");
              link.classList.add("cursor-not-allowed");
              link.classList.add("opacity-50");
              link.textContent = "ADDED TO CART !";
            } else {
              console.log("ITEM_NOT_ADDED_SUCCESSFULLY");
            }
          });
      });
    });
  };
  window.addEventListener("load", handleAddToCart);

  /*
   * Remove TextContents from Buttons (Remove) in Cart - CART
   */
  const removeTextContentsCart = () => {
    const removeButtons = document.querySelectorAll(".bi-x-lg");
    removeButtons.forEach(function (btn) {
      btn.textContent = "";
    });
  };
  window.addEventListener("load", removeTextContentsCart);

  /*
   * Handle BackgroundColor Sync on Row Hover in Cart - CART
   */
  const syncBgColorRowCart = () => {
    const rows = document.querySelectorAll(".table-row-group .table-row");
    rows.forEach((row) => {
      let input = row.querySelector("input");
      row.addEventListener("mouseenter", function () {
        input.classList.replace("bg-secondary", "bg-secondary-100");
      });
      row.addEventListener("mouseleave", function () {
        input.classList.replace("bg-secondary-100", "bg-secondary");
      });
    });
  };
  window.addEventListener("load", syncBgColorRowCart);

  /*
   * Checkout - CHECKOUT
   */

  /*
   * Function to Format Address From Popup
   */
  function extractAddressFromPopup(popupContent) {
    // Créer un élément div temporaire pour analyser le contenu HTML de la popup
    var tempDiv = document.createElement("div");
    tempDiv.innerHTML = popupContent;

    // Récupérer les éléments avec les classes spécifiques
    var nameElement = tempDiv.querySelector(".name");
    var addressElement = tempDiv.querySelector(".address");

    // Extraire les valeurs des éléments
    var name = nameElement ? nameElement.textContent.trim() : "";
    var address = addressElement ? addressElement.textContent.trim() : "";

    // Vérifier si le nom de rue commence par un nombre suivi d'un espace
    var streetRegex = /^\d+\b/;
    var streetMatch = address.match(streetRegex);

    var street = "";
    if (streetMatch) {
      var streetNumber = streetMatch[0];
      var streetName = name;
      street = streetNumber + " " + streetName;
    } else {
      street = name;
    }

    // Extraire les parties spécifiques de l'adresse
    var parts = address.split(", ");
    var city = parts[parts.length - 5];
    var postalCode = parts[parts.length - 2];
    var country = parts[parts.length - 1];

    // Construire le résultat final
    var result = "";
    if (street) {
      result += street + ", ";
    }
    if (city) {
      result += city + ", ";
    }
    if (postalCode) {
      result += postalCode + ", ";
    }
    if (country) {
      result += country;
    }

    return result;
  }

  const showMapLeaflet = () => {
    // Initialize default icons
    delete L.Icon.Default.prototype._getIconUrl;
    L.Icon.Default.mergeOptions({
      iconUrl: markerIcon,
      iconRetinaUrl: markerIcon2x,
      shadowUrl: markerShadow,
    });

    const API_KEY = "pk.da60081c90327265b082bda3adbdbcec";

    let isMap = document.getElementById("map");

    if (isMap) {
      // Create TileLayer
      const map = L.map("map", { zoomControl: false }).setView(
        [48.62944030761719, 2.421143054962158],
        11
      );
      L.tileLayer(
        `https://{s}-tiles.locationiq.com/v3/dark/r/{z}/{x}/{y}.png?key=${API_KEY}`,
        {
          maxZoom: 19,
          attribution:
            '&copy; <a href="http://www.locationiq.com/">LocationIQ</a>',
        }
      ).addTo(map);

      // Create Marker on Map
      const marryMixPopup =
        "<p class='font-semibold text-center uppercase'> MarryMix </p> <p>Rue Charlie Chaplin, 91000 EVRY, FRANCE </p>";
      const customOptions = {
        // maxWidth: "auto",
        className: "",
        keepInView: true,
      };

      let markers = [];

      const marker = L.marker([48.62944030761719, 2.421143054962158], {
        title: "MarryMix",
      });
      marker.addTo(map);
      marker.bindPopup(marryMixPopup, customOptions).openPopup();
      markers.push(marker);

      if (window.location.href.includes("checkout")) {
        let latCustomer = parseFloat(localStorage.getItem("latCustomer"));
        let lngCustomer = parseFloat(localStorage.getItem("lngCustomer"));

        if (latCustomer && lngCustomer) {
          // Create MarkerCustomer on Map
          const customerPopup =
            "<p class='font-semibold text-center uppercase'> Your Delivery Address </p>";

          const markerCustomer = L.marker([latCustomer, lngCustomer], {
            title: "Your Delivery Address",
          });
          markerCustomer.addTo(map);
          markerCustomer.bindPopup(customerPopup, customOptions).openPopup();
          markers.push(markerCustomer);

          // Viewing Behavior
          // Create A Limit of Bounds Empty
          let bounds = L.latLngBounds([]);

          // Adding Positions to Limits of Bounds
          markers.forEach(function (marker) {
            bounds.extend(marker.getLatLng());
          });

          // FitBounds for viewing all Markers
          if (bounds.isValid()) {
            map.fitBounds(bounds);
            setTimeout(function () {
              map.setZoom(10);
            }, 100);
          }
        }
      }

      // Select Input "Delivery Address"
      const deliveryAddressInput = document.querySelector(
        "#delivery_delivery_address"
      );
      const deliveryLatitudeInput = document.querySelector(
        "#delivery_latitude_address"
      );
      const deliveryLongitudeInput = document.querySelector(
        "#delivery_longitude_address"
      );

      if (deliveryAddressInput) {
        deliveryAddressInput.value = "";
        // Listen on New Markers & Get Infos
        map.on("layeradd", function (e) {
          if (e.layer instanceof L.Marker) {
            let markerResult = e.layer;
            let coordinates = markerResult.getLatLng();
            if (coordinates) {
              // Fill Hidden Inputs to Matrix
              deliveryLatitudeInput.value = coordinates.lat;
              deliveryLongitudeInput.value = coordinates.lng;
              // Store Localisation Information
              localStorage.setItem("latCustomer", coordinates.lat.toString());
              localStorage.setItem("lngCustomer", coordinates.lng.toString());
            }
            let popup = markerResult.getPopup();
            if (popup) {
              let popupContent = popup.getContent();
              let addressFormatted = extractAddressFromPopup(popupContent);
              deliveryAddressInput.value = addressFormatted;
              // deliveryAddressInput.disabled = true;
            }
          }
        });

        // Plugin AutoComplete Search
        L.control.geocoder(API_KEY).addTo(map);

        if (window.location.href.includes("payment/success")) {
          localStorage.removeItem("latCustomer");
          localStorage.removeItem("lngCustomer");
        }
      }
    }
  };
  window.addEventListener("load", showMapLeaflet);

  /*
   * Configurate a DatePicker for Delivery Date - DELIVERY INFORMATION
   */
  const configurateDatePicker = () => {
    const datePickerEl = document.querySelector(".datepickerInput");
    if (datePickerEl) {
      new Datepicker(datePickerEl, {
        orientation: "bottom right",
        format: "yyyy-mm-dd",
        autohide: true,
        weekStart: 1,
        minDate: new Date().toISOString().split("T")[0],
      });
      // Adjust Position
      const datePickerWindow = document.querySelector(".datepicker-dropdown");
      datePickerWindow.classList.remove("top-0");
      // datePickerWindow.offsetTop = datepickerEl.offsetTop;
      datePickerWindow.style.top = `${datePickerEl.offsetTop}px`;
      const datePickerCalendar = document.querySelector(".datepicker-picker");
      datePickerCalendar.classList.replace("bg-white", "bg-primary-500/50");
    }
  };
  window.addEventListener("load", configurateDatePicker);

  /*
   * Handle AJAX for Cookies Consent
   */
  const cookieConsentHandler = () => {
    const form = document.getElementById("cookie-consent-form");
    if (form) {
      form.addEventListener("submit", function (event) {
        event.preventDefault();

        let formData = new FormData(form);
        // Fetch
        fetch("/cookie-consent", {
          method: "POST",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
          },
          body: formData,
        })
          .then(function (response) {
            if (response.ok) {
              return response.text();
            }
            throw new Error("Error: " + response.status);
          })
          .then(function (data) {
            console.log(data);
            form.style.display = "none";
          })
          .catch(function (error) {
            console.error(error);
          });
      });
    }
  };

  window.addEventListener("load", cookieConsentHandler);
})();
