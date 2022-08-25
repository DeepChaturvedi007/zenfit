import React, { useCallback, useMemo, Fragment, useState, useEffect } from 'react';

import moment from 'moment';

import RecipeTooltip from './RecipeTooltip';

import ModalContainer from "../../containers/Modal";
import ActivityTypes from "../../constants/ActivityTypes";
import CookingTimes from "../../constants/CookingTimes";
import imagePlaceholder from "../../assets/meal_thumbnail.png";
import { RecipeItem, RecipeCheckbox, RecipeCheckboxEmpty, RecipeDetails, RecipeActions, RecipeTitle, RecipeFoodLable, NewLabel, RecipeImage, RecipeCookingTime } from '../../components/Recipe';
import { Button, FlatButton, IconBox } from '../../components/UI';
import { ReactComponent as CloseIcon } from "remixicon/icons/System/close-fill.svg";
import { ReactComponent as HearLineIcon } from "remixicon/icons/System/heart-line.svg";
import { ReactComponent as HearFillIcon } from "remixicon/icons/System/heart-fill.svg";
import { ReactComponent as ThumbsDownIcon } from "remixicon/icons/System/thumb-down-line.svg";
import { DishPopover } from "../../components/Dish";
import { FoodLabel } from '../../constants/FoodLabels'

const Recipe = React.memo(({ recipe, disabled, selected, selectedRecipe, mealId, dishId, onSelect, onRemove, onRemoved, onPreferenceChoose, onSelectRecipe }) => {
  const modal = ModalContainer.useContainer();
  const [submitting, setSubmitting] = useState(false);
  const added = selectedRecipe.filter((item) => {
    return item.id === recipe.id
  }).length !== 0;
  useEffect(() => {
    const activity = modal.activity;
    setSubmitting(added && activity);
  }, [modal.activity]);

  const handleSelect = () => {
    onSelectRecipe(recipe, false)
  }

  const handleRemove = () => {
    // const column = dish => dish.recipe === recipe.id;
    // modal.setActivity(ActivityTypes.MEAL_RECIPE, { recipe, mealId, dishId });
    // onRemove(mealId, column)
    onSelectRecipe(recipe, true)
  };

  const handleFavorite = useCallback(async () => {
    if (onPreferenceChoose) {
      await onPreferenceChoose(recipe, 'favorite')
    }
  }, [recipe]);

  const handleDislike = useCallback(async () => {
    if (!window.confirm('Are you sure you want to dislike this recipe?')) {
      return;
    }

    if (onPreferenceChoose) {
      await onPreferenceChoose(recipe, 'dislike')
    }
  }, [recipe]);

  const getLabel = (name) => {
    if(FoodLabel[name]) {
      return FoodLabel[name]
    }
    else {
      return {
        color: '#545454',
        name: 'NW',
        backgroundColor: '#d4d4d4'
      }
    }
  }
  const hasCanHitMacros = recipe.hasOwnProperty('canHitMacros');
  const cantHitMacros = hasCanHitMacros && !recipe.canHitMacros;
  const cookingTime = useMemo(() => CookingTimes[recipe.cookingTime], [recipe.cookingTime]);
  const buttonProps = {
    type: "button",
    loading: submitting,
    modifier: selected ? (added ? "default" : "blue") : (added ? "blue" : "default"),
    disabled: disabled,
  };

  if (!buttonProps.loading) {
    buttonProps.onClick = selected ? handleRemove : handleSelect;
  }

  return (
    <RecipeItem id={recipe.id}>
      {/* {selected ? (
        <RecipeCheckboxEmpty />
      ) : (
        <RecipeCheckbox
          onChange={() => {
            onSelectRecipe(recipe)
          }}
        />
      )} */}
      <RecipeTooltip recipe={recipe}>
        <RecipeImage>
          <img src={recipe.image || imagePlaceholder} alt={recipe.name} />
        </RecipeImage>
      </RecipeTooltip>
      <RecipeDetails>
        {moment().subtract(1, 'month').isBefore(recipe.createdAt.date) && (
          <NewLabel>NEW</NewLabel>
        )}
        <RecipeTitle>
          {recipe.name+" "}
          {recipe.foodPreferences.map((item, i) => {
            return(
              <RecipeFoodLable color = {getLabel(item).color} backgroundColor = {getLabel(item).backgroundColor} key={i}>
                {getLabel(item).name}
              </RecipeFoodLable>
            )
          })}
        </RecipeTitle>
        <RecipeActions>
          <FlatButton type="button" onClick={handleFavorite} icon small>
            <IconBox size={14} p={5}>
              {recipe.favorite ? <HearFillIcon /> : <HearLineIcon />}
            </IconBox>
          </FlatButton>
          <FlatButton type="button" onClick={handleDislike} icon small>
            <IconBox size={14} p={5}>
              <ThumbsDownIcon />
            </IconBox>
          </FlatButton>
        </RecipeActions>
      </RecipeDetails>
      <IconBox size={16} withTooltip>
        {cantHitMacros && (
          <Fragment>
            <CloseIcon />
            <DishPopover>
              {`Unfortunately this recipe can't hit the desired macro split`}
            </DishPopover>
          </Fragment>
        )}
      </IconBox>
      {cookingTime && (
        <RecipeCookingTime>
          {cookingTime} min
        </RecipeCookingTime>
      )}
      <Button {...buttonProps} disabled={disabled}>
        <span>{selected ? (added ? "Select" : "Selected") : added ? "Selected" : "Select"}</span>
      </Button>
    </RecipeItem>
  );
});

export default Recipe;
