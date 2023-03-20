({
    init: function () {
        if (AVAIL("cs", "sheets")) {
            leftSide("fas fa-fw fa-table", i18n("cs.cs"), "#cs", "tt");
        }

        if (AVAIL("cs", "sheet", "PUT")) {
            loadSubModules("cs", [
                "sheet",
            ], this);
        } else {
            moduleLoaded("cs", this);
        }
    },

    route: function (params) {
        $("#subTop").html("");
        $("#altForm").hide();

        document.title = i18n("windowTitle") + " :: " + i18n("cs.cs");

        $("#mainForm").html(i18n("cs.cs"));

        loadingDone();
    },
}).init();