import { CLIENT_FOOD_PREFERENCES, MEALS_PER_DAY } from './constants/Meal';

export const FILTERS = {
  'pending': 'Getting Started',
  'all': 'All Active Clients',
  'need-plans': 'Update Plans',
  'missing-checkin': 'Missed Check-in',
  'progress': 'New Check-Ins',
  'unanswered': 'Messages',
  'old-chats': 'Old Chats',
  'ending': 'Ending',
  'payments': 'Payment Failed',
  'custom': 'Reminders'
};

export const STRIPE_CONNECT_URL = 'https://connect.stripe.com/oauth/authorize?response_type=code&client_id=ca_CUqvV11imT09L70DpkEtDFXwrbcobsIo&scope=read_write';
export const S3_BEFORE_AFTER_IMAGES = 'https://zenfit-images.s3.eu-central-1.amazonaws.com/before-after-images/client/photo/';
export const S3_BEFORE_AFTER_IMAGES1 = 'https://zenfit-images.s3.eu-central-1.amazonaws.com/before-after-images/';
export const DEFAULT_IMAGE_URL = '/bundles/app/1456081788_user-01.png';
export const CLIENTS_LIST_FETCH_LIMIT = 35;

export const WEEK_DAYS = {
  1: 'Monday',
  2: 'Tuesday',
  3: 'Wednesday',
  4: 'Thursday',
  5: 'Friday',
  6: 'Saturday',
  7: 'Sunday'
};

export const UPDATE_PLANS_SCHEDULE = {
  4: 'Every 4 weeks',
  6: 'Every 6 weeks',
  8: 'Every 8 weeks'
};

export const DURATION = {
  1: '1 month',
  2: '2 months',
  3: '3 months',
  4: '4 months',
  5: '5 months',
  6: '6 months',
  7: '7 months',
  8: '8 months',
  9: '9 months',
  10: '10 months',
  11: '11 months',
  12: '12 months',
}

export const LEVELS = {
  1: 'Beginner',
  2: 'Intermediate',
  3: 'Advanced'
};

export const GENDER = {
  1: 'Female',
  2: 'Male'
}

export const GOAL_TYPE = {
  1: 'Lose Weight',
  2: 'Gain Weight'
}

export const LOCALES = {
  'en': 'English',
  'da_DK': 'Danish',
  'sv_SE': 'Swedish',
  'nb_NO': 'Norwegian',
  'fi_FI': 'Finnish',
  'nl_NL': 'Dutch',
  'de_DE': 'German'
}

export const MEASURING_SYSTEM = {
  1: 'Metric (kg/cm)',
  2: 'Imperial (lbs/inch)'
}

export const WORKOUTS_PER_WEEK = {
  1: 1,
  2: 2,
  3: 3,
  4: 4,
  5: 5,
  6: 6,
  7: 7
}

export const EXPERIENCE_LEVELS = {
  1: 'Beginner',
  2: 'Intermediate',
  3: 'Advanced'
}

export const WORKOUT_LOCATIONS = {
  1: 'Gym',
  2: 'At home',
  3: 'Outdoor'
}

export const ACTIVITY_LEVEL = {
  1: 'Low activity & doesn’t work out',
  2: 'Sedentary job & 1-2 workouts/week',
  3: 'Moderate active job & 3-4 workouts/week',
  4: 'Quite active job & work out most days',
  5: 'Hard physical job & work out most day'
}

export const MEASURE_SYSTEM_HEIGHT = {
  1: "Cm",
  2: "Feet"
}

export const MEASURE_SYSTEM_WEIGHT = {
  1: "Kg",
  2: "Lbs"
}

export const LBS_TO_KG = 0.45359237;
export const FEET_TO_CM = 30.48;

export const CLIENT_INFO_FIELDS = {
  name: {
    type: 'input',
    label: 'Name',
    key: 'name'
  },
  measuringSystem: {
    type: 'select',
    label: 'Measuring System',
    key: 'measuringSystem',
    options: MEASURING_SYSTEM
  },
  email: {
    type: 'input',
    label: 'Email',
    key: 'email'
  },
  phone: {
    type: 'input',
    label: 'Phone',
    key: 'info.phone'
  },
  age: {
    type: 'number',
    label: 'Age',
    key: 'info.age'
  },
  gender: {
    type: 'select',
    label: 'Gender',
    key: 'info.gender',
    options: GENDER
  },
  goalType: {
    type: 'select',
    label: 'Goal Type',
    key: 'info.goalType',
    options: GOAL_TYPE
  },
  height: {
    type: 'number',
    label: 'Height',
    valueType: MEASURE_SYSTEM_HEIGHT,
    key: 'info.height'
  },
  feet: {
    type: 'number',
    label: 'Feet',
    key: 'info.feet'
  },
  inches: {
    type: 'number',
    label: 'inches',
    key: 'info.inches'
  },
  startWeight: {
    type: 'number',
    label: 'Start Weight',
    valueType: MEASURE_SYSTEM_WEIGHT,
    key: 'info.startWeight'
  },
  goalWeight: {
    type: 'number',
    label: 'Goal Weight',
    valueType: MEASURE_SYSTEM_WEIGHT,
    key: 'info.goalWeight'
  },
  locale: {
    type: 'select',
    label: 'Language',
    key: 'info.locale',
    options: LOCALES
  },
  workoutLocation: {
    type: 'select',
    label: 'Workout Location',
    key: 'info.workoutLocation',
    options: WORKOUT_LOCATIONS
  },
  experienceLevel: {
    type: 'select',
    label: 'Workout experience',
    key: 'info.experienceLevel',
    options: EXPERIENCE_LEVELS
  },
  workoutsPerWeek: {
    type: 'select',
    label: 'Workouts Per Week',
    key: 'info.workoutsPerWeek',
    options: WORKOUTS_PER_WEEK
  },
  numberOfMeals: {
    type: 'select',
    label: 'Meals Per Day',
    key: 'info.numberOfMeals',
    options: MEALS_PER_DAY
  },
  clientFoodPreferences: {
    type: 'multiSelect',
    label: 'Food Preferences',
    key: 'info.clientFoodPreferences',
    options: CLIENT_FOOD_PREFERENCES
  },
  activityLevel: {
    type: 'select',
    label: 'Activity Level (PAL)',
    key: 'info.activityLevel',
    options: ACTIVITY_LEVEL
  },
  lifestyle: {
    type: 'textarea',
    label: 'Lifestyle',
    key: 'info.lifestyle'
  },
  dietStyle: {
    type: 'textarea',
    label: 'Diet Style',
    key: 'info.dietStyle'
  },
  motivation: {
    type: 'textarea',
    label: 'Motivation',
    key: 'info.motivation'
  },
  experience: {
    type: 'textarea',
    label: 'Experience',
    key: 'info.experience'
  },
  exercisePreferences: {
    type: 'textarea',
    label: 'Exercise Preferences',
    key: 'info.exercisePreferences'
  },
  injuries: {
    type: 'textarea',
    label: 'Injuries',
    key: 'info.injuries'
  },
  other: {
    type: 'textarea',
    label: 'Other',
    key: 'info.other'
  },
};

export const CLIENT_SETUP_FIELDS = {
  startDate: {
    type: 'date',
    label: 'Start Date',
    key: 'startDate.date'
  },
  duration: {
    type: 'select',
    label: 'Duration',
    key: 'duration',
    options: DURATION
  },
  endDate: {
    type: 'dateVal',
    label: 'End date',
    key: 'endDate.date'
  }
}

export const CLIENT_NOTES_FIELDS = {
  startDate: {
    type: 'date',
    label: 'Start Date',
    key: 'startDate.date'
  },
  duration: {
    type: 'select',
    label: 'Duration',
    key: 'duration',
    options: DURATION
  },
  endDate: {
    type: 'dateVal',
    label: 'End date',
    key: 'endDate.date'
  },
  tags: {
    type: 'multiSelect',
    label: 'Tags',
    key: 'tags',
    options: [],
    creatable: true
  },
  note: {
    type: 'textarea',
    label: 'Private Note',
    key: 'info.notes.note'
  },
  dialogMessage: {
    type: 'static',
    label: 'Lead Info',
    key: 'info.notes.dialogMessage'
  },
  salesNotes: {
    type: 'static',
    label: 'Sales Note',
    key: 'info.notes.salesNotes'
  },
};

export const CLIENT_WORKOUT_METAS = {
  level: ['BEGINNER', 'INTERMEDIATE', 'ADVANCED'],
  location: ['GYM', 'AT HOME', 'OUTDOOR'],
  gender: ['FEMALE', 'MALE'],
  workoutsPerWeek: 'DAYS',
  duration: 'DAY SPLIT',
  type: ''
};

export const PERIOD_GRAPH_TOGGLE = {
  12: 'ALL',
  6: '6m',
  3: '3m'
}
