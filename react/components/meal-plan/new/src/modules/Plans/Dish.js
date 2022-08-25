import React, { memo } from "react";
import { Draggable } from "react-beautiful-dnd";
import {
  Dish,
  DishCaption,
  DishImageBox,
  DishImage,
  DishActions,
  DishOverlay,
  DishTitle,
  DishImageOverlay,
  DishPopover
} from "../../components/Dish";
import { IconButton, VerticalDivider } from "../../components/UI";

const Card = memo(({ dish, index, onShowRecipes, onRemove, onPlanView }) => {
  const diff = Math.round(Math.abs(dish.ideal_kcals - dish.totals.kcal));
  return (
    <Draggable
      draggableId={dish.id}
      index={index}
    >
      {(provided, snapshot) => (
        <Dish
          ref={provided.innerRef}
          {...provided.draggableProps}
          {...provided.dragHandleProps}
        >
          <DishImageBox>
            <DishImageOverlay/>
            <DishImage src={dish.image} />
            {diff > 30 && (
              <DishOverlay>
                <span>{diff} kcals off</span>
              </DishOverlay>
            )}
            <DishTitle>{dish.name}</DishTitle>
          </DishImageBox>
          <DishCaption>
            <h5>{dish.name}</h5>
            <span>{dish.totals.kcal} kcals</span>
          </DishCaption>
          <DishActions>
            <IconButton onClick={onShowRecipes}>restaurant</IconButton>
            <VerticalDivider/>
            <IconButton onClick={onPlanView}>remove_red_eye</IconButton>
            <VerticalDivider/>
            <IconButton onClick={onRemove}>close</IconButton>
          </DishActions>
          <DishPopover hidden={snapshot.isDragging}>
            {dish.totals.kcal} kcals: {dish.name}
          </DishPopover>
        </Dish>
      )}
    </Draggable>
  );
});

export default Card;
