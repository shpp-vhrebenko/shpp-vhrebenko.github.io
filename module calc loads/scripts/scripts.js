"use strict";angular.module("calculationLoadsApp",["ngRoute","LocalStorageModule"]).config(["$routeProvider","localStorageServiceProvider",function(o,e){o.when("/",{templateUrl:"views/main.html",controller:"MainCtrl",controllerAs:"main"}).when("/about",{templateUrl:"views/about.html",controller:"AboutCtrl",controllerAs:"about"}).otherwise({redirectTo:"/"}),e.setPrefix("LoadsCalc")}]),angular.module("calculationLoadsApp").controller("MainCtrl",["$scope","$http","localStorageService",function(o,e,t){var s=o;s.house={},s.house.housetop={},s.house.housetop.lathing={},s.showSpecification=!1,t.get("house.housetop.roof_pitch")&&(o.house.housetop.roof_pitch=t.get("house.housetop.roof_pitch")),o.$watch("house.housetop.roof_pitch",function(o){t.set("house.housetop.roof_pitch",o)}),t.get("house.housetop.rafters_width")&&(o.house.housetop.rafters_width=t.get("house.housetop.rafters_width")),o.$watch("house.housetop.rafters_width",function(o){t.set("house.housetop.rafters_width",o)}),t.get("house.housetop.roof_width")&&(o.house.housetop.roof_width=t.get("house.housetop.roof_width")),o.$watch("house.housetop.roof_width",function(o){t.set("house.housetop.roof_width",o)}),t.get("house.housetop.rafters_step")&&(o.house.housetop.rafters_step=t.get("house.housetop.rafters_step")),o.$watch("house.housetop.rafters_step",function(o){t.set("house.housetop.rafters_step",o)}),t.get("house.housetop.roof_power")&&(o.house.housetop.roof_power=t.get("house.housetop.roof_power")),o.$watch("house.housetop.roof_power",function(o){t.set("house.housetop.roof_power",o)}),t.get("house.housetop.snow_power")&&(o.house.housetop.snow_power=t.get("house.housetop.snow_power")),o.$watch("house.housetop.snow_power",function(o){t.set("house.housetop.snow_power",o)}),t.get("house.housetop.lathing.lathing_step")&&(o.house.housetop.lathing.lathing_step=t.get("house.housetop.lathing.lathing_step")),o.$watch("house.housetop.lathing.lathing_step",function(o){t.set("house.housetop.lathing.lathing_step",o)}),t.get("house.housetop.lathing.lathing_b")&&(o.house.housetop.lathing.lathing_b=t.get("house.housetop.lathing.lathing_b")),o.$watch("house.housetop.lathing.lathing_b",function(o){t.set("house.housetop.lathing.lathing_b",o)}),t.get("house.housetop.lathing.lathing_h")&&(o.house.housetop.lathing.lathing_h=t.get("house.housetop.lathing.lathing_h")),o.$watch("house.housetop.lathing.lathing_h",function(o){t.set("house.housetop.lathing.lathing_h",o)}),this.calcLoads=function(t){console.log(t),e.post("action.php",t).success(function(e){o.response=e,o.showSpecification=!0;var t=e.calcLoads.Condition,s=e.calcLoads;if(t.first_condition&&t.second_condition&&t.third_condition)o.message="done_message",o.showCondition=!0,o.error_message="Условия выполнены!";else if(s.correctValue){var h=s.correctValue.correctRafters_width,u=s.correctValue.correctRafters_step;o.error_message='Рекомендуется увеличить параметр  " ширинa стропильной ноги " до значения '+h+' см  или уменьшить параметр " шаг стропил " до значения '+u+" см",o.showCondition=!0,o.message="error_message"}console.log(e)})}}]),angular.module("calculationLoadsApp").controller("AboutCtrl",function(){this.awesomeThings=["HTML5 Boilerplate","AngularJS","Karma"]});