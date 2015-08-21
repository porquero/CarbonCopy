$(window).load(function () {
    $("section, article, aside").customScrollbar({
        updateOnWindowResize: true,
        swipeSpeed: 2,
        wheelSpeed: 80,
        skin: "default-skin"
    });
});
