(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

new Vue({
    el: 'body',
    data: {
        isTeam: 0,
        alias: '',
        genderSelect: 'M',
        ageCategorySelect: 0,
        ageMin: 0,
        ageMax: 0,
        gradeSelect: 0,
        gradeMin: 0,
        gradeMax: 0,
        error: '',
        grades: [{ value: 0, text: no_grade }, { value: 2, text: '7 Kyu' }, { value: 3, text: '6 Kyu' }, { value: 4, text: '5 Kyu' }, { value: 5, text: '4 Kyu' }, { value: 6, text: '3 Kyu' }, { value: 7, text: '2 Kyu' }, { value: 8, text: '1 Kyu' }, { value: 9, text: '1 Dan' }, { value: 10, text: '2 Dan' }, { value: 11, text: '3 Dan' }, { value: 12, text: '4 Dan' }, { value: 13, text: '5 Dan' }, { value: 14, text: '6 Dan' }, { value: 15, text: '7 Dan' }, { value: 16, text: '8 Dan' }],
        ageCategories: [{ value: 0, text: no_age }, { value: 1, text: childs }, { value: 2, text: students }, { value: 3, text: adults }, { value: 4, text: masters }, { value: 5, text: custom }],

        genders: [{ value: 'M', text: male }, { value: 'F', text: female }, { value: 'X', text: mixt }],
        categoryFullName: 'Individual Men',
        url: '/api/v1/category/create'

    },
    computed: {
        categoryFullName: function categoryFullName() {
            var teamText = this.isTeam == 1 ? team : single;
            var ageCategoryText = '';
            var gradeText = '';

            if (this.ageCategorySelect != 0) {
                if (this.ageCategorySelect == 5) {
                    ageCategoryText = ' - ' + age + ' : ';
                    if (this.ageMin != 0 && this.ageMax != 0) {
                        if (this.ageMin == this.ageMax) {
                            ageCategoryText += this.ageMax + ' ' + years;
                        } else {
                            ageCategoryText += this.ageMin + ' - ' + this.ageMax + ' ' + years;
                        }
                    } else if (this.ageMin == 0 && this.ageMax != 0) {
                        ageCategoryText += ' < ' + this.ageMax + ' ' + years;
                    } else if (this.ageMin != 0 && this.ageMax == 0) {
                        ageCategoryText += ' > ' + this.ageMin + ' ' + years;
                    } else {
                        ageCategoryText = '';
                    }
                } else {
                    ageCategoryText = this.getSelectText(this.ageCategories, this.ageCategorySelect);
                }
            }

            if (this.gradeSelect == 3) {
                gradeText = ' - ' + grade + ' : ';
                if (this.gradeMin != 0 && this.gradeMax != 0) {
                    if (this.gradeMin == this.gradeMax) {
                        gradeText += this.getSelectText(this.grades, this.gradeMin);
                    } else {
                        gradeText += this.getSelectText(this.grades, this.gradeMin) + ' - ' + this.getSelectText(this.grades, this.gradeMax);
                    }
                } else if (this.gradeMin == 0 && this.gradeMax != 0) {
                    gradeText += ' < ' + this.getSelectText(this.grades, this.gradeMax);
                } else if (this.gradeMin != 0 && this.gradeMax == 0) {
                    gradeText += ' > ' + this.getSelectText(this.grades, this.gradeMin);
                } else {
                    gradeText = '';
                }
            }

            return teamText + ' ' + this.getSelectText(this.genders, this.genderSelect) + ' ' + ageCategoryText + ' ' + gradeText;
        }
    },

    methods: {
        addCategory: function addCategory() {
            var _this = this;

            this.error = '';
            // Get Get Category Name and Id
            // var url = '/api/v1/category/team/' + this.isTeam + '/gender/' + this.genderSelect
            //     + '/age/' + this.ageCategorySelect + '/' + this.ageMin + '/' + +this.ageMax
            //     + '/grade/' + this.gradeSelect + '/' + this.gradeMin + '/' + this.gradeMax;


            dualListIds = [];
            $(".listbox-filter-disabled > option").each(function () {
                dualListIds.push(this.value);
            });

            var categoryData = {
                "isTeam": this.isTeam,
                "gender": this.genderSelect,
                "alias": this.alias,
                "ageCategory": this.ageCategorySelect,
                "ageMin": this.ageMin,
                "ageMax": this.ageMax,
                "gradeCategory": this.gradeSelect,
                "gradeMin": this.gradeMin,
                "gradeMax": this.gradeMax
            };

            this.$http.post(this.url, categoryData).then(function (response) {
                if (dualListIds.indexOf('' + response.data.id) == -1) {
                    var option;
                    console.log(_this.alias);
                    if (_this.alias != '') option = '<option value=' + response.data.id + ' selected>' + _this.alias + '</option>';else option = '<option value=' + response.data.id + ' selected>' + _this.categoryFullName + '</option>';
                    // console.log(option);
                    dualList.append(option);
                    dualList.bootstrapDualListbox('refresh');
                } else {
                    // Print message
                    _this.error = " Ya existe el elemento";
                }
            });
        },

        // $.getJSON(url, function (data) {
        //     if (dualListIds.indexOf('' + data.id) == -1){
        //         var option = '<option value=' + data.id + ' selected>' + this.categoryFullName + '</option>';
        //         // console.log(option);
        //         dualList.append(option);
        //         dualList.bootstrapDualListbox('refresh');
        //     }else{
        //         // Print message
        //         this.error =" Ya existe el elemento";
        //     }
        // }.bind(this));


        decodeHtml: function decodeHtml(html) {
            var txt = document.createElement("textarea");
            txt.innerHTML = html;
            return txt.value;
        },
        getSelectText: function getSelectText(myArray, val) {
            var newVal = '';
            myArray.map(function (el) {
                if (val == el.value) {
                    newVal = el.text;
                }
            });
            return newVal;
        },
        resetModalValues: function resetModalValues() {
            this.isTeam = 0;
            this.genderSelect = 'M';
            this.ageCategorySelect = 0;
            this.ageMin = 0;
            this.ageMax = 0;
            this.gradeSelect = 0;
            this.gradeMin = 0;
            this.gradeMax = 0;
        }
    },
    filters: {
        selectText: function selectText(val, myArray) {
            return this.getSelectText(myArray, val);
        },
        html: function html(_html) {
            return this.decodeHtml(_html);
        }
    }
});

},{}]},{},[1]);

//# sourceMappingURL=categoryCreate.js.map
