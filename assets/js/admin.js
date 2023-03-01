// Selectors
let header = document.querySelector(".header");
let sidebar = document.querySelector(".sidebar");
let menuBtn = document.querySelector("#menuBtn");
let searchBtn = document.querySelector(".bx-search");
let showSidebarBtnMobile = document.querySelector("#mobile_showSidebar");
let closeSidebarBtnMobile = document.querySelector(".btn-close");
let logoDetails = document.querySelector(".logo-details");
let logoIcon = document.querySelector("#logo_icon");
let logoName = document.querySelector(".logo_name");
let navList = document.querySelector(".nav-list");
let content = document.querySelector(".content");

// SIDEBAR Change MenuBtn
function menuBtnChange() {
  if (sidebar.classList.contains("open")) {
    menuBtn.classList.replace("bx-menu", "bx-menu-alt-right"); //replacing the iocns class
  } else {
    menuBtn.classList.replace("bx-menu-alt-right", "bx-menu"); //replacing the iocns class
  }
}

//  SIDEBAR Handle Link Active
const navLinks = document.querySelectorAll(".nav_link");

function colorLinkActive() {
  const navLinks = document.querySelectorAll("a.nav_link");
  if (navLinks) {
    navLinks.forEach((link) => {
      const url = window.location.pathname;
      if (link.getAttribute("href") === url) {
        link.classList.add("active");
      } else {
        link.classList.remove("active");
      }
    });
  }
}

window.addEventListener("load", colorLinkActive);

// SIDEBAR HandleResize & MediaQueries
function handleResize() {
  if (window.matchMedia("(max-width: 420px)").matches) {
    sidebar.classList.add("open");
    // SIDEBAR Handle Click MenuBtn & SearchBtn Opening
    menuBtn.removeEventListener("click", () => {
      header.classList.toggle("open");
      sidebar.classList.toggle("open");
      content.classList.toggle("open");
      menuBtnChange(); //calling the function(optional)
    });
    searchBtn.removeEventListener("click", () => {
      header.classList.toggle("open");
      sidebar.classList.toggle("open");
      content.classList.toggle("open");
      menuBtnChange(); //calling the function(optional)
    });
    showSidebarBtnMobile.addEventListener("click", () => {
      sidebar.classList.toggle("show");
    });
    closeSidebarBtnMobile.addEventListener("click", () => {
      sidebar.classList.toggle("show");
    });
  } else {
    sidebar.classList.remove("open");
    // SIDEBAR Handle Click MenuBtn & SearchBtn Opening
    menuBtn.addEventListener("click", () => {
      header.classList.toggle("open");
      sidebar.classList.toggle("open");
      content.classList.toggle("open");
      menuBtnChange(); //calling the function(optional)
    });
    searchBtn.addEventListener("click", () => {
      header.classList.toggle("open");
      sidebar.classList.toggle("open");
      content.classList.toggle("open");
      menuBtnChange(); //calling the function(optional)
    });
    showSidebarBtnMobile.removeEventListener("click", () => {
      sidebar.classList.toggle("show");
    });
    closeSidebarBtnMobile.removeEventListener("click", () => {
      sidebar.classList.toggle("show");
    });
  }
}

// SIDEBAR Handle Resize on Load, Resize
window.addEventListener("load", handleResize);
window.addEventListener("resize", handleResize);
