var component = "<div class='popover tour'>" +
    "<div class='arrow'></div>" +
    "<h3 class='popover-title'></h3> " +
    "<div class='popover-content'></div>" +
    "<div class='popover-navigation' style='text-align: right;margin:10px;'>" +
        "<button class='btn btn-default next' data-role='next'>Next</button>" +
        "<button class='hidden btn btn-default done' data-role='end'>Done</button>" +
    "</div>";

var tour = new Tour({
    storage : false,
    template: component,
    onShown: function (tour) {
        var step = tour._options.steps[tour.getCurrentStep()];
        if(step.last) {
            $(".popover-navigation button.next").remove();
            $(".popover-navigation button.done").removeClass("hidden");
        }
    },
    onEnd: function (tour) {
        var step = tour._options.steps[tour.getCurrentStep()];
        if(step.type == 'workoutPlan') {
            $("#finishedTour").modal();
            setFinishedTour();
        }
    }
});


function initBootstrapTour() {
    tour.addSteps([
        {
            element: ".workout-hint-actions .btnStart",
            placement: "bottom",
            title: "Start from scratch",
            content: "Click here to start creating your first workout plan - you can always save it as a template later and reuse it on future clients!"
        },
        {
            element: ".workout-hint-actions .js-use-template",
            placement: "bottom",
            title: "Use an existing workout plan",
            content: "You can also use an existing workout template to create your new workout plan.",
            last: true,
        },
        {
            element: "#exercise_container",
            placement: "right",
            title: "Exercise library",
            content: "Welcome to the Workout Plan Builder! To the left here we have all the exercises in our system - you can simply drag and drop exercises to the workout plan!"
        },
        {
            element: ".workouts-header-right .js-add-plan",
            placement: "left",
            title: "Add another workout day",
            content: "Here you can add another workout day - for instance Biceps/Back day."
        },
        {
            element: ".workouts-header-right .js-save-template",
            placement: "bottom",
            title: "Save Workout Plan as template",
            content: "Here you can save your workout as template, so you can reuse it on all your clients!",
            type: 'workoutPlan'
        },
        {
            element: ".responsive-tabs .meal-tab",
            placement: "bottom",
            title: "Meal Plan",
            content: "You can also check out the Meal Plan tab, where you can design diet plans for your clients.",
        },
        {
            element: ".responsive-tabs .body-tab",
            placement: "bottom",
            title: "Body Progress",
            content: "Or start tracking your client\'s body measurements.",
        },
        {
            element: ".responsive-tabs .documents-tab",
            placement: "bottom",
            title: "Documents",
            content: "Alternatively, you can click here to upload documents straight to your client\'s app.",
            type: 'workoutPlan',
            last: true,
        }

    ]);

    tour.init();
    tour.start();
}

function initClientOverviewTour() {
    tour.addSteps([
        {
            element: ".table-container",
            placement: "bottom",
            title: "Welcome to the Client Overview",
            content: "All of your clients will be listed here, and you can easily get an overview of updates you or your client has made, that you might need to act upon.",
        },
        {
            element: "td.workout-plan-updated:last",
            placement: "bottom",
            title: "Workout Plan Last Updated",
            content: "For instance, you can see when there was last made a change to the client's Workout Plan - same goes for Meal Plan and Body Progress",
        },
        {
            element: "td.client-status:last",
            placement: "bottom",
            title: "Has your client created a login for the Zenfit App",
            content: "This one is very important - this status tells whether the client has created a login for the Zenfit App - if status is not 'Active', tell your client to check his/her email (also Spam-folder) and follow the instructions :)",
            last: true,
            endTour: true,
            type: 'clientOverview'
        }

    ]);

    tour.init();
    tour.start();
}

function initClientInfoTour() {
    tour.addSteps([
        {
            element: "#general_info",
            placement: "top",
            title: "Welcome to your client\'s info",
            content: "You can save your client\'s info here - if your client has answered your questionnaire, the information will be stored here.",
        },
        {
            element: ".responsive-tabs .workout-tab",
            placement: "bottom",
            title: "Workout Plan",
            content: "Now, let us check out the Workout Plan tab. Here you can create a workout plan for your client!",
            last: true,
        }

    ]);

    tour.init();
    tour.start();
}

function setShowWorkoutPlanTour()
{
    $.ajax({
        type: "POST",
        url: setShowWorkoutPlanTourUrl,
        success: function (res) {
            console.log(res);
        }
    });
}

function setFinishedTour()
{
    $.ajax({
        type: "POST",
        url: '/setShowWorkoutPlanTour',
        success: function (res) {
            console.log(res);
        }
    });
}