#!/usr/bin/env python3.5
import numpy as np
import sys, json
import random

plan = json.loads(sys.argv[1])
target = plan['target']
ingredients = plan['ingredients']

running = True
success = False
loop = 0
response = []
all_ingredients = {}

def get_ingredients(tweakable):
    return [ing for ing in all_ingredients if all_ingredients[ing]['tweak'] == tweakable]

def parse_ingredients():
    for ingredient in ingredients:
        all_ingredients[ingredient['id']] = {
            'carbohydrate': ingredient['macros']['carbohydrate'],
            'protein': ingredient['macros']['protein'],
            'fat': ingredient['macros']['fat'],
            'tweak': ingredient['tweak']
        }

# initial functions to prepare ingredients
parse_ingredients()

def get_remaining_macros():
    macros = {'carbohydrate': 0, 'protein': 0, 'fat': 0}
    remaining = [ing for ing in all_ingredients if all_ingredients[ing]['tweak'] == False]
    for r in remaining:
        macros['carbohydrate'] = macros['carbohydrate'] + all_ingredients[r]['carbohydrate']
        macros['protein'] = macros['protein'] + all_ingredients[r]['protein']
        macros['fat'] = macros['fat'] + all_ingredients[r]['fat']

    return np.array([macros['carbohydrate'],macros['protein'],macros['fat']])

def get_tweakable_ingredients():
    a = np.empty((0,3))
    for i in get_ingredients(True):
        a = np.append(a, np.array([[
            all_ingredients[i]['carbohydrate'],
            all_ingredients[i]['protein'],
            all_ingredients[i]['fat']
        ]]), axis=0)

        response.append(i)

    return a.T

def check_result(coefficients, all_ingredients, target):
    for coef in coefficients:
        if coef <= 0.1 or coef > 5:
            return False

    return True

while running:
    a = get_tweakable_ingredients()
    r = get_remaining_macros()

    carbohydrate = target['carbohydrate']
    protein = target['protein']
    fat = target['fat']

    macro_target = np.array([carbohydrate, protein, fat])
    y = np.subtract(macro_target, r)
    '''
    print("Ingredients to tweak")
    print(a)
    print(" ")

    print("TARGET")
    print(macro_target)
    print(" ")

    print("Remaining ingredients")
    print(r)
    print(" ")

    print("Our Y value, subtracted remaining ingredients")
    print(y)
    print(" ")
    '''
    try:
        result = np.linalg.solve(a,y)
        # check if result is good
        approved = check_result(result, all_ingredients, macro_target)
        if approved:
            success = True
    except:
        # to avoid indent exception
        loop = loop

    running = False

if success:
    dictionary = dict(zip(response, result))
    print(json.dumps(dictionary))
