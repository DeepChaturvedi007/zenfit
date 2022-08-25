export const CREATE_ACCOUNT_FIELDS = (t, locale) => {
    return {
        name: {
            label: t('client.survey.fullName'),
            name: 'name',
            type: 'input'
        },
        email: {
            label: t('client.survey.email'),
            name: 'email',
            type: 'email'
        },
        phone: {
            label: t('client.survey.phone'),
            name: 'phone',
            type: 'phone'
        },
        password: {
            label: t('client.activation.password'),
            name: 'password',
            type: 'password'
        },
        passwordConfirm: {
            label: t('client.activation.confirmPassword'),
            name: 'passwordConfirm',
            type: 'password'
        },
    }
}

export const GENDERS = (t) => {
    return {
        1: t('client.survey.female'),
        2: t('client.survey.male')
    }
}

export const MEASURE_SYSTEM = (t) => {
    return {
        1: `${t('client.survey.metric')} (kg/cm)`,
        2: `${t('client.survey.imperial')} (lbs/inches)`
    }
}

export const ACTIVITY_LEVEL = (t) => {
    return {
        1: t('client.survey.activityLevels.1'),
        2: t('client.survey.activityLevels.2'),
        3: t('client.survey.activityLevels.3'),
        4: t('client.survey.activityLevels.4'),
        5: t('client.survey.activityLevels.5')
    }
}

export const GOAL_WEIGHT_GAIN = (t, measureType) => {
    return {
        4: measureType == 2 ? t('client.survey.primaryGoals.imperial.4') : t('client.survey.primaryGoals.metric.4'),
        5: measureType == 2 ? t('client.survey.primaryGoals.imperial.5') : t('client.survey.primaryGoals.metric.5'),
    }
}
export const GOAL_WEIGHT_MAINTAIN = (t, measureType) => {
    return {
        3: measureType == 2 ? t('client.survey.primaryGoals.imperial.3') : t('client.survey.primaryGoals.metric.3'),
    }
}
export const GOAL_WEIGHT_LOSE = (t, measureType) => {
    return {
        1: measureType == 2 ? t('client.survey.primaryGoals.imperial.1') : t('client.survey.primaryGoals.metric.1'),
        2: measureType == 2 ? t('client.survey.primaryGoals.imperial.2') : t('client.survey.primaryGoals.metric.2'),
    }
}

export const GOAL_WEIGHT_CONVERT = {
    1: 1,
    2: 0.5,
    4: 0.5,
    5: 1
}


const WORKOUT_EXPERIENCE_LEVEL = (t) => {
    return {
        1: t('client.survey.workoutExperienceLevels.1'),
        2: t('client.survey.workoutExperienceLevels.2'),
        3: t('client.survey.workoutExperienceLevels.3')
    }
}

const WORKOUT_LOCATIONS = (t) => {
    return {
        1: t('client.survey.workoutLocations.1'),
        2: t('client.survey.workoutLocations.2'),
        3: t('client.survey.workoutLocations.3')
    }
}

const WORKOUT_PER_WEEK = (t) => {
    return {
        1: 1,
        2: 2,
        3: 3,
        4: 4,
        5: 5,
        6: 6,
        7: 7
    }
}


export const WORKOUT_PREFERENCES_FIELDS = (t) => {
    return {
        injuries: {
            label: t('client.survey.injuries'),
            name: 'injuries',
            type: 'input'
        },
        workoutExperienceLevel: {
            label: t('client.survey.workoutExperienceLevel'),
            name: 'experienceLevel',
            type: 'select',
            options: WORKOUT_EXPERIENCE_LEVEL(t)
        },
        workoutElaboration: {
            label: t('client.survey.workoutExperiencePlaceholder'),
            name: 'experience',
            type: 'input'
        },
        workoutPreferences: {
            label: t('client.survey.workoutPreferences'),
            name: 'exercisePreferences',
            type: 'input'
        },
        workoutLocations: {
            label: t('client.survey.selectWorkoutLocation'),
            name: 'workoutLocation',
            type: 'select',
            options: WORKOUT_LOCATIONS(t)
        },
        workoutsPerWeek: {
            label: t('client.survey.workoutsPerWeek'),
            name: 'workoutsPerWeek',
            type: 'select',
            options: WORKOUT_PER_WEEK(t)
        },
    }
}

export const NUMBER_OF_MEALS_PR_DAY = {
    3: 3,
    4: 4,
    5: 5,
    6: 6,
}

export const DIET_PREFERENCES_ARR = ['isPescetarian', 'isVegetarian', 'isVegan', 'none']
export const DIET_PREFERENCES = (t) => {
    return {
        none: t('client.survey.none'),
        isPescetarian: t('client.survey.pescetarianType'),
        isVegetarian: t('client.survey.vegetarianType'),
        isVegan: t('client.survey.veganType'),
    }
}

export const FOOD_PREFERENCES = (t) => {
    return {
        avoidLactose: t('client.survey.lactose'),
        avoidGluten: t('client.survey.gluten'),
        avoidNuts: t('client.survey.nuts'),
        avoidEggs: t('client.survey.eggs'),
        avoidPig: t('client.survey.pig'),
        avoidShellfish: t('client.survey.shellfish'),
        avoidFish: t('client.survey.fish')
    }
}

export const SIGNUP_FLOW_FIELDS = {
    account: {
        name: null,
        email: null,
        phone: null,
    },
    general: {
        gender: null,
        age: null,
        photo: null,
        height: null,
        feet: null,
        inches: null,
        startWeight: null,
        activityLevel: null,
        measuringSystem: null
    },
    photos: {
        front: null,
        back: null,
        side: null
    },
    workout: {
        injuries: null,
        experienceLevel: null,
        experience: null,
        exercisePreferences: null,
        workoutLocation: null,
        workoutsPerWeek: null
    },
    goal: {
        primaryGoal: null,
        goalWeight: null
    },
    diet: {
        dietStyle: null,
        numberOfMeals: null,
        clientFoodPreferences: null,
        dietPreference: null
    },
    other: {
        lifestyle: null,
        motivation: null,
        termsAccepted: null,
        questions: null,
    },
}
