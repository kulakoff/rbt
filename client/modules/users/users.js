({
    init: function () {
        leftSide("fas fa-fw fa-user", i18n("users.users"), "#users");
        moduleLoaded("users", this);
    },

    editUser: function (uid) {
        cardForm({
            title: "Заголовок карточки",
            topApply: true,
//            target: "#altForm",
            footer: true,
            borderless: true,
            fields: [
                {
                    id: "uid",
                    type: "text",
                    readonly: true,
                    value: uid,
                    title: i18n("users.uid"),
                },
                {
                    id: "login",
                    type: "text",
                    readonly: true,
                    value: "admin",
                    title: i18n("users.login"),
                },
                {
                    id: "realName",
                    type: "text",
                    readonly: false,
                    value: "",
                    title: i18n("users.realName"),
                    placeholder: i18n("users.realName"),
                },
                {
                    id: "eMail",
                    type: "email",
                    readonly: false,
                    value: "",
                    title: i18n("users.eMail"),
                    placeholder: i18n("users.eMail"),
                    validate: (v) => {
                        return $.trim(v) !== "";
                    }
                },
                {
                    id: "phone",
                    type: "tel",
                    readonly: false,
                    value: "",
                    title: i18n("users.phone"),
                    placeholder: i18n("users.phone"),
                    button: {
                        class: "fas fa-fw fa-square",
                        click: function () {
                            alert("hello!");
                        }
                    }
                },
                {
                    id: "delete",
                    type: "select",
                    readonly: false,
                    value: "",
                    title: i18n("users.delete"),
                    options: [
                        {
                            value: "",
                            text: "",
                        },
                        {
                            value: "yes",
                            text: i18n("yes"),
                        },
                    ]
                },
            ],
            callback: result => {
                console.log(result);
                $("#altForm").hide();
            },
            cancel: () => {
                $("#altForm").hide();
            }
        }).show();
    },

    addUser: function () {

    },

    setPassword: function (uid) {

    },

    contextItemClick: function (uid, action) {
        console.log(uid, action);
    },

    route: function (params) {
        document.title = i18n("windowTitle") + " :: " + i18n("users.users");

        GET("accounts", "users").done(response => {
            cardTable({
                addButton: {
                    title: i18n("users.addUser"),
                    click: () => {
                        console.log(1);
                    }
                },
                title: i18n("users.users"),
                filter: true,
                itemsPerPage: 25,
                pagesCount: 10,
                columns: [
                    {
                        title: i18n("users.uid"),
                    },
                    {
                        title: i18n("users.login"),
                    },
                    {
                        title: i18n("users.realName"),
                        fullWidth: true,
                    },
                    {
                        title: i18n("users.eMail"),
                    },
                    {
                        title: i18n("users.phone"),
                    },
                ],
                rows: () => {
                    let rows = [];

                    for (let i = 0; i < response.users.length * 101; i++) {
                        rows.push({
                            cols: [
                                {
//                                    data: response.users[i].uid,
                                    data: i,
                                    click: this.editUser,
                                },
                                {
//                                    data: response.users[i].login,
                                    data: "login #" + i,
                                    click: this.editUser,
                                    nowrap: true,
                                },
                                {
                                    data: "нет",
//                                    data: response.users[i].realName?response.users[i].realName:i18n("no"),
                                },
                                {
                                    data: "нет",
//                                    data: response.users[i].eMail?response.users[i].eMail:i18n("no"),
                                    nowrap: true,
                                },
                                {
                                    data: "нет",
//                                    data: response.users[i].phone?response.users[i].phone:i18n("no"),
                                    nowrap: true,
                                },
                            ],
                            dropDown: [
                                {
                                    icon: "fas fa-tv",
                                    title: "Action 1",
                                    click: this.contextItemClick,
                                },
                                {
                                    icon: "fas fa-coffee",
                                    action: "coffee",
                                    title: "Action 2",
                                    click: this.contextItemClick,
                                },
                                {
                                    title: "-",
                                },
                                {
                                    icon: "fas fa-home",
                                    title: "Action 4",
                                    text: "text-primary",
                                    click: this.contextItemClick,
                                },
                                {
                                    icon: "fas fa-trash-alt",
                                    title: "Action disabled",
                                    text: "text-danger",
                                    disabled: true,
                                    click: this.contextItemClick,
                                },
                            ],
                            uid: response.users[0].uid,
                        });
                    }

                    return rows;
                },
                target: "#mainForm",
            });
        }).fail(response => {
            if (response && response.responseJSON && response.responseJSON.error) {
                error(i18n("errors." + response.responseJSON.error), "[" + i18n("users.users") + "]: " + i18n("error"), 30);
            } else {
                error(i18n("errors.unknown"), "[" + i18n("users.users") + "]: " + i18n("error"), 30);
            }
        }).always(() => {
            loadingDone();
        });
    }
}).init();