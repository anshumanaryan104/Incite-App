function setTheme() {
    var theme = localStorage.getItem('theme');
    console.log(theme);
    if(theme=='light'){
      var linkElement = document.getElementById('theme-style');
      linkElement.href = base_url+"/admin-assets/vendor/css/rtl/theme-dark.css";
      localStorage.setItem('theme', 'dark');
      document.cookie = "theme=dark; path=/";
      $(".icon-switch").addClass('ti-sun');
      $(".icon-switch").removeClass('ti-moon-stars');
    //   document.documentElement.classList.remove('ti-moon-stars');
    //   document.documentElement.classList.add('ti-sun');
      $("html").removeClass('light-style');
      $("html").addClass('dark-style');
    }else if(theme=='dark'){
      var linkElement = document.getElementById('theme-style');
      linkElement.href = base_url+"/admin-assets/vendor/css/rtl/theme.css";
      localStorage.setItem('theme', 'light');
      document.cookie = "theme=light; path=/";
      $(".icon-switch").removeClass('ti-sun');
      $(".icon-switch").addClass('ti-moon-stars');
      $("html").addClass('light-style');
      $("html").removeClass('dark-style');
    }else{
      var linkElement = document.getElementById('theme-style');
      linkElement.href = base_url+"/admin-assets/vendor/css/rtl/theme-dark.css";
      localStorage.setItem('theme', 'dark');
      document.cookie = "theme=dark; path=/";
      $(".icon-switch").addClass('ti-sun');
      $(".icon-switch").removeClass('ti-moon-stars');
      $("html").removeClass('light-style');
      $("html").addClass('dark-style');
    }    
}
  
//   // Check for stored theme on page load
//   var storedTheme = localStorage.getItem('theme');
//   if(storedTheme=='light'){
//       var linkElement = document.getElementById('theme-style');
//       linkElement.href = base_url+"/admin-assets/vendor/css/rtl/theme.css";
//   }else{
//       var linkElement = document.getElementById('theme-style');
//       linkElement.href = base_url+"/admin-assets/vendor/css/rtl/theme-dark.css";
//   }
  
//   // Listen for changes in the user's preferred color scheme
//   const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
//   darkModeMediaQuery.addListener((e) => {
//     const darkModeOn = e.matches;
//     if (darkModeOn) {
//       $("html").addClass('dark-style');
//       $("html").removeClass('light-style');
//       localStorage.setItem('theme', 'dark');
//       document.cookie = "theme=dark; path=/";
//     } else {
//       $("html").addClass('light-style');
//       $("html").removeClass('dark-style');
//       localStorage.setItem('theme', 'light');
//       document.cookie = "theme=light; path=/";
//     }
//   });
  
//   // Add appropriate classes to html tag based on user's preferred color scheme
//   if (darkModeMediaQuery.matches) {
//     $("html").addClass('dark-style');
//     $("html").removeClass('light-style');
//   } else {
//     $("html").addClass('light-style');
//     $("html").removeClass('dark-style');
//   }
  