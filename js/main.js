//submitting any form resets its validation
$('form').submit(function(){
    $('form .error').text("");
    $('div#alert_box').hide();
});
//fade in the alert box.
$('div#alert_box').fadeIn(200);
//table col mouseenter
$(".data_table th").mouseenter(function(){
    $(this).parents("table").find("tr td:nth-child("+($(this).index()+1)+")")
        .css("background-color", "#c5d5ff")
    ;
    $(this)
        .css("background-color", "#5985ff")
    ;

});

//table col mouseleave
$(".data_table th").mouseleave(function(){
    $(this).parents("table").find("tr td:nth-child("+($(this).index()+1)+")")
        .css("background-color", "#aec4ff")
    ;
    $(this)
        .css("background-color", "#82a2fa")
    ;

});
//table row mouseenter
$(".data_table td").mouseenter(function(){
    $(this).parent().children("td")
        .css("background-color", "#c5d5ff")
    ;
    $(this)
        .css("background-color", "#d3dfff")
    ;
});
//table row mouseleave
$(".data_table td").mouseleave(function(){
    $(this).parent().children("td")
        .css("background-color", "#aec4ff")
    ;
});
//expandable images
function exp_img_define(){
    //remove old event handlers to reduce clogging
    $('img.expandable').off("click");
    $('img.expanded').off("click");
    //add new handlers
    $('img.expandable').on("click", function(){
        $(this) //when clicked, do this
            .removeClass("expandable")
            .addClass("expanded")
            .css("width",$(this).attr("width")+"px")
            .css("position", "fixed")
            .css("margin","auto")
            .css("top",($('body').height()-$(this).height())/2)
            .css("left",($('body').width()-$(this).width())/2)
            .css("box-shadow", "none")
            .css("border", "2px solid black")
            .css("z-index",3)
            .attr("title","")
        ;
        exp_img_define();
    });
    $('img.expanded').on("click", function(){
        $(this) //when clicked, do this
            .removeClass("expanded")
            .addClass("expandable")
            .css("width",$(this).attr("default-width")+"px")
            .css("position", "static")
            .css("margin","0")
            .css("box-shadow", "4px 4px 2px #a7a7a7")
            .css("border", "none")
            .attr("title","click to enlarge")
        ;
        if ($(this).hasClass("img_left")) $(this).css("margin-right", "10px");
        exp_img_define();
    });
}
exp_img_define();

//table row click handling
$("table.clickable td").click(function(){
    var table = $(this).parents("table");
    var dest = table.attr("data-destination"); //URL of destination
    var field = table.attr("data-field"); //GET key
    //get column number
    var col_index = table.find("tr:first th:contains("+field+")").index()+1;
    //get data for GET
    var data = $(this).parents("tr").children("td:nth-child("+col_index+")").text();
    //perform GET request
    location.assign(""+dest+"?"+field.toLowerCase()+"="+data);
});
//disable qnext clicks
$("a.qnext").click(function(event){
    event.preventDefault();
});
//checkbox check onclick
//check for radio disabled
if ($(".radio input:disabled").length == 0)
{
$(".radio .checkbox").click(function(){
    //uncheck all
    $(this).parents("form").find(".checkbox").removeClass("checked");
    //check this
    $(this).addClass("checked");
});
}
//checkbox default check
//uncheck all
$(".radio input").parent().children(".checkbox").removeClass("checked");
//check default.
$(".radio input:checked").parent().children(".checkbox").addClass("checked");
//rate 10 slider readback
$(".rate10input").on("input", function(){
    $(this).parents(".rate10").find(".readout").text($(this).val());
});
//auto generate instructions
$("#auto_generate_instr").click(function(){
    var default_text = $(this).parents("form").find("textarea").attr("data-default");
    $(this).parents("form").find("textarea").text(default_text);
});

//normal check box
$(".check_box .checkbox").click(function(){
    //check this
    $(this).toggleClass("checked");
});
//check default.
$(".check_box input:checked").parent().children(".checkbox").addClass("checked");