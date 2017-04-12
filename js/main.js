$(function () {


    $("#addImages").click(function () {
        $("figure.clicked_image > img").each(function () {
            let img_src = this.src;
            window.parent.send_to_editor("<img src='"+img_src+"' height='200' width='300'/>");
        });

        $("figure.clicked_image").removeClass("clicked_image");
        $(this).prop("disabled", true);
        window.parent.tb_remove();
    });

    $(document).on("click", "figure", function () {
        let qtd = $("figure.clicked_image").length;
        let addImages = $("#addImages");

        if($(addImages).prop("disabled") && qtd > 0)
        {
            $(addImages).prop("disabled", false);
        }else if(qtd === 0)
        {
            $(addImages).prop("disabled", true);
        }
    });

    $("#search_bar").on("keyup", function () {
        let search = $("#search");
        if($(this).val().length > 0)
        {
            $(search).prop("disabled", false);

            if ( event.which === 13 ) {
                $(search).click();
            }

        }else $(search).prop("disabled", true);
    });

    $("#search").on("click", function () {
        let location = $("#location").val();
        let search_term = $("#search_bar").val();
        let collectionToSearch = $("#wichCollection").val();

        let main_div = $("#main_div");
        $(main_div).empty();

        let data = {data: search_term, operation: "search", collectionToSearch: collectionToSearch};
        $.post(ajaxurl, data, function (response) {
            response = response.substr(0, (response.length - 1));
            $("#backHome").prop("disabled", false);
            $(main_div).append(response);

            $("#addImages").prop("disabled", true);
            $("#search_bar").select();
        });
    });

    $("#backHome").click(function () {
        $(this).prop("disabled", true);
        $("#search").prop("disabled", true);
        let wichCollection = $("#wichCollection").val();
        let data = {operation: "backHome", wichCollection: wichCollection};

        $.post(ajaxurl, data, function (response) {
            response = response.substr(0, (response.length - 1));

            let main_div = $("#main_div");

            $(main_div).empty();
            $(main_div).append(response);

            $("#search_bar").val("");
        });
    });

    $("#wichCollection").on("change", function () {

        let main_div = $("#main_div");

        $(main_div).empty();

        data = {data: $(this).val(), operation: "wichCollection"};
        $.post(ajaxurl, data, function (response) {
            response = response.substr(0, (response.length - 1));
            $(main_div).append(response);

            $("#addImages").prop("disabled", true);
            $("#search_bar").select();
        });
    });

    function changeClass(obj, oldClass, newClass)
    {
        $(obj).removeClass(oldClass);
        $(obj).addClass(newClass);
    }
});