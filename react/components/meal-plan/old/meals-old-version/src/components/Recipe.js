// eslint-disable-next-line
import React from 'react';
import styled from 'styled-components';
import Tooltip from '@material-ui/core/Tooltip';
import { withStyles, makeStyles } from '@material-ui/core/styles';

export const RecipeItem = styled.div`
  display: flex;
  align-items: center;
  padding: 8px 0;

  & + & {
    border-top: 1px solid #f2f6fa;
  }
`;

export const RecipeCheckbox = styled.input.attrs({ type: 'checkbox' })`
  margin: 0px 5px 0px 0px!important;
`;

export const RecipeCheckboxEmpty = styled.div`
  width: 18px
`;

export const RecipeDetails = styled.div`
  display: flex;
  flex: 1 1 auto;
  flex-direction: column;
`;

export const RecipeActions = styled.div`
  display: flex;
`;

export const RecipeTitle = styled.h4`
  font-size: 14px;
  line-height: 20px;
  color: #4f566b;
  font-weight: 400;
  margin: 0 16px;
  & + ${RecipeActions} {
    margin-left: 10px;
  }
`;

export const RecipeFoodLableTooltip = withStyles((theme) => ({
  popper: {
    zIndex: 10000
  },
  tooltip: {
    color: 'white',
    boxShadow: theme.shadows[1],
    fontSize: 11,
  },
}))(Tooltip);

export const RecipeFoodLable = styled.span`
  font-size: 10px;
  font-weight: bolder;
  color: ${props => props.color};
  background-color: ${props => props.backgroundColor};
  border-radius: 3px;
  border: 1px solid ${props => props.backgroundColor};
  padding: 3px;
  margin: 0px 6px 0px 0px
`;

export const NewLabel = styled.span`
  font-size: 10px;
  line-height: 9px;
  border-radius: 2px;
  border: 1px solid #0662FF;
  color: #0662FF;
  padding: 2px;
  margin: 1px 16px;
  width: 30px;
  text-align: center;
  font-weight: bolder
`;
export const RecipeImage = styled.div`
  border-radius: 4px;
  min-width: 44px;
  width: 44px;
  height: 44px;
  overflow: hidden;

  img {
    display: block;
    object-fit: cover;
    width: 100%;
    height: 100%;
  }
`;

export const RecipeCookingTime = styled.div`
  background-color: #f6f9fc;
  color: #8898aa;
  border-radius: 2px;
  display: inline-block;
  font-size: 11px;
  margin-right: 6px;
  margin-left: 6px;
  white-space: nowrap;
  padding: 4px 6px;
  line-height: 1;
  font-weight: 700;
`;
