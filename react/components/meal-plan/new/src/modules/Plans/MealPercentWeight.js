import React, { memo, useCallback, useState, useMemo, useRef } from "react";
import styled from "styled-components";
import update from "react-addons-update";
import delay from "lodash/delay";
import MealPlansContainer from "../../containers/MealPlans";
import RangeSlider from "../../components/RangeSlider";
import Popup from "../../Popup";
import { FormGroup, FormLabel as BaseFormLabel } from "../../components/Form";
import { Button, Link } from "../../components/UI";

const FormLabel = styled(BaseFormLabel)`
  display: flex;
  justify-content: space-between;
  margin: 5px 0;
`;

const Badge = styled.span`
  display: inline-block;
  font-family: Roboto, sans-serif;
  font-size: 12px;
  font-weight: normal;
  font-stretch: normal;
  font-style: normal;
  line-height: normal;
  letter-spacing: 0.05px;
  color: #333333;
  padding: 5px;
  border-radius: 2px;
  background-color: #f5f5f5;
`;

const labelRenderer = (value, multiplier = 100) => {
  let results = parseFloat(value);

  if (typeof results !== 'number' || !isFinite(results)) {
    results = .0;
  }

  const fraction = 0;//results % 1 !== 0 ? 1 : 0;
  return `${(results * multiplier).toFixed(fraction)}%`;
};

const FormContainer = styled.div`
  width: 232px;
  padding: 0 5px;

  ${FormGroup} {
    margin-bottom: 16px;
  }
`;

const FormFooter = styled.div`
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 20px;
`;

const TotalLabel = styled.span`
  background-color: ${props => props.isDanger ? '#d14' : '#32325d'};
  border-radius: 4px;
  display: inline-block;
  line-height: 1;
  color: #fff;
  font-size: 14px;
  padding: 6px 10px;
  font-weight: 500;
`;

const MealPercentWeight = memo(({ meal, planId }) => {
  const container = MealPlansContainer.useContainer();
  const popupRef = useRef();
  const [values, setValues] = useState([]);
  const [isSubmitting, setSubmitting] = useState(false);

  const baseSliderProps = {
    min: 0.1,
    max: 1,
    step: .01,
    labelRenderer,
  };

  const total = useMemo(() => values.reduce((total, item) => (total + item.value), 0), [values]);
  const isInvalid = useMemo(() => Math.round(total * 100) !== 100, [total]);

  const onValueChange = (index) => {
    return (value) => {
      setValues(update(values, {
        [index]: {
          value: { $set: Math.round(value * 100) / 100 },
        },
      }));
    };
  };

  const onSave = useCallback(() => {
    if (isInvalid) {
      return;
    }

    setSubmitting(true);

    delay(() => {
      const meals = values.map(({ id, value }) => ({ id, value }));
      container.updateProgressWeights(planId, meals).then(() => {
        setSubmitting(false);
        delay(popupRef.current.close, 50);
        // popupRef.current.close();
      });
    }, 250);
  }, [values]);

  const onOpen = () => {
    setValues(update(values, {
      $set: [...container.progressWeightsByPlan(planId)],
    }));
  };

  const renderTrigger = useCallback(() => {
    return (
      <Link href="#">{labelRenderer(meal.percent_weight)}</Link>
    );
  }, [meal.percent_weight]);

  const renderBody = useCallback(() => {
    return (
      <FormContainer>
        {values.map((item, index) => (
          <FormGroup key={`input_${index}`}>
            <FormLabel>
              {item.name}
              <Badge>
                {Math.round(item.kcalsPerPercent * item.value * 100)} kcal
              </Badge>
            </FormLabel>
            <RangeSlider
              value={item.value}
              onChange={onValueChange(index)}
              {...baseSliderProps}
            />
          </FormGroup>
        ))}
        <FormFooter>
          <TotalLabel isDanger={isInvalid}>
            {labelRenderer(total)}
          </TotalLabel>
          <Button
            disabled={isInvalid || isSubmitting}
            onClick={onSave}
            type="button"
          >
            {isSubmitting ? 'Saving...' : 'Save'}
          </Button>
        </FormFooter>
      </FormContainer>
    );
  }, [values, isSubmitting, isInvalid]);

  return (
    <Popup
      ref={popupRef}
      canClose={!isSubmitting}
      renderTrigger={renderTrigger}
      renderBody={renderBody}
      onOpen={onOpen}
      style={{zIndex:110}}
    />
  )
});

export default MealPercentWeight;
