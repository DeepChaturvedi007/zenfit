import React, { PureComponent } from 'react';
import InfiniteScroll from 'react-infinite-scroller';
import Recipe from './Recipe';
import CookingTimes from '../../constants/CookingTimes';
import AvoidPreferences from '../../constants/AvoidPreferences';
import Popup from '../../Popup';
import Loading from '../../Loading';
import { ModalBody, ModalHeader, ModalActions } from '../../components/Modal';
import { Input } from '../../components/Form';
import { FlatButton, IconBox, Center } from '../../components/UI';
import * as api from '../../utils/api';
import { ReactComponent as TimerIcon } from 'remixicon/icons/System/timer-line.svg';
import { ReactComponent as SettingsIcon } from 'remixicon/icons/System/settings-2-line.svg';
import { ReactComponent as HearLineIcon } from 'remixicon/icons/System/heart-line.svg';
import { ReactComponent as HearFillIcon } from 'remixicon/icons/System/heart-fill.svg';
import ModalTypes from '../../constants/ModalTypes';
import _ from 'lodash';

const avoidPreferenceOptions = [
  { value: '', label: 'All Recipes' },
  ...AvoidPreferences,
];

const cookingTimeOptions = Object.keys(CookingTimes).reduce((list, value) => {
  list.push({ value, label: `${CookingTimes[value]} min` });
  return list;
}, [{ value: '', label: 'All Recipes' }]);

const ABORT_MESSAGE = 'Operation canceled by the user.';
class Recipes extends PureComponent {
  constructor(props) {
    super(props);

    this.state = {
      data: [],
      hasMore: true,
      filters: {
        avoid: [],
        cookingTime: '',
        keyword: '',
        favorites: false,
        q: ''
      },
      limit: 20,
      offset: 0,
      loading: false,
    };
  }

  componentDidMount() {
    this.fetch();
  }

  componentWillUpdate(nextProps, nextState, nextContext) {
    const { modal } = nextProps;
    if (modal.isLoading !== nextState.loading) {
      modal.loading(nextState.loading);
    }
  }

  // Lifecycle
  componentWillUnmount() {
    this._cancelCurrentQuery();
  }

  // Getters
  get plan() {
    const { meal, mealPlans } = this.props;
    return mealPlans.planById(meal.planId)
  }

  get queryParams() {
    const plan = this.plan;
    const { meal } = this.props;
    const {
      filters,
      limit,
      offset,
    } = this.state;
    return {
      // Permanent params
      plan: plan.id,
      type: +meal.type > 0 ? +meal.type : undefined,
      locale: plan.locale,
      macroSplit: meal.macroSplit,
      considerIngredients: true,
      mealPlan: meal.id,
      // Query params
      q: filters.q || undefined,
      avoid: filters.avoid.join(',') || undefined,
      favorites: filters.favorites || undefined,
      cookingTime: filters.cookingTime || undefined,
      // Pagination params
      offset,
      limit
    }
  }

  fetch = () => {
    const { data: currentData, offset, limit } = this.state;
    this._cancelCurrentQuery();
    this.setState({ loading: true });
    this.fetchSource = api.CancelToken.source();
    const cancelToken = this.fetchSource.token;

    api.fetchRecipes(this.queryParams, { cancelToken })
      .then(({ data }) => data)
      .then(data => {
        const newData = _.unionBy([...currentData, ...data], 'id')
        this.setState({
          data: newData,
          offset: offset + limit,
          hasMore: (newData.length > currentData.length && data.length >= limit),
          loading: false
        });
      })
      .catch(error => {
        if (error.message === ABORT_MESSAGE && !this.fetchSource.token.reason) {
          // Just skip it. The new request is in progress
        } else {
          this.setState({ loading: false });
        }
        return [];
      });
  };

  // Helpers
  _updateFilters = (filters, debounce = 0) => {
    clearTimeout(this.timer);
    this.setState(
      { filters, data: [], offset: 0 },
      () => {
        this.timer = setTimeout(() => {
          this.fetch();
        }, debounce)
      }
    );
  };

  _cancelCurrentQuery = () => {
    if (this.fetchSource) {
      this.fetchSource.cancel(ABORT_MESSAGE);
    }
  };

  _getModalScroll = () => {
    const { modalRef } = this.props;
    return modalRef.current && modalRef.current.portal.content;
  };

  // Handlers
  handleLoadMore = () => {
    this.fetch();
  };

  handlePreferenceUpdate = async (recipe, option) => {
    this.setState({ loading: true });

    const formData = new FormData();

    formData.set('id', recipe.id);
    formData.set('option', option);

    const result = await api.updateRecipePreference(formData)
      .then(({ data }) => data)
      .catch(error => {
        console.error('[Recipes Preference]', error);
      });

    let updates = [];
    switch (option) {
      case 'favorite': {
        updates = this.state.data.map(item => ({
          ...item,
          favorite: item.id === recipe.id ? result.favorite : item.favorite
        }));
        break;
      }
      case 'dislike': {
        updates = this.state.data.filter(item => item.id !== recipe.id);
        break;
      }
      default: {
        break;
      }
    }
    this.setState({ data: updates, loading: false });
  };

  handleKeywordChange = (event) => {
    const { filters: currentFilters } = this.state;
    const filters = {
      ...currentFilters,
      q: event.target.value
    };
    this._updateFilters(filters, 300);
  };

  handleFavorites = () => {
    const { filters: currentFilters } = this.state;
    const filters = {
      ...currentFilters,
      favorites: !currentFilters.favorites
    };
    this._updateFilters(filters);
  };

  handleAvoidSelect = (event, { value }) => {
    const { filters: currentFilters } = this.state;
    let avoid = [...currentFilters.avoid];

    if (!value) {
      avoid = [];
    } else if (avoid.includes(value)) {
      avoid = avoid.filter(curVal => curVal !== value);
    } else {
      avoid.push(value)
    }
    const filters = {
      ...currentFilters,
      avoid
    };
    this._updateFilters(filters);
  };

  handleCookingTimeSelect = (event, { value }) => {
    const { filters: currentFilters } = this.state;
    const filters = {
      ...currentFilters,
      cookingTime: value
    };
    this._updateFilters(filters);
  };

  handleRecipeRemoved = () => {
    this.props.onClose();
  };

  // Renders
  render() {
    const { meal, dish, modal, mealPlans, selectedRecipe, onSelectRecipe} = this.props;
    const {
      hasMore,
      filters,
      data = [],
      loading
    } = this.state;

    const {
      q,
      cookingTime,
      avoid,
      favorites
    } = filters;
    return (
      <InfiniteScroll
        hasMore={hasMore && data.length && !loading}
        // hasMore={false}
        useWindow={false}
        getScrollParent={this._getModalScroll}
        initialLoad={modal.is(ModalTypes.RECIPES)}
        loadMore={this.handleLoadMore}
        loader={
          <Center key={`more:loader:0`}>
            <Loading />
            <h4>Loading more recipes...</h4>
          </Center>
        }
      >
        <ModalHeader>
          <Input
            placeholder="Search recipe..."
            value={q}
            onChange={this.handleKeywordChange}
            type="text"
          />
          <ModalActions>
            <Popup
              position="left"
              value={cookingTime}
              options={cookingTimeOptions}
              onSelect={this.handleCookingTimeSelect}
              renderTrigger={this.renderCookingActionTrigger}
            />
            <Popup
              multiple
              position="left"
              value={avoid}
              options={avoidPreferenceOptions}
              onSelect={this.handleAvoidSelect}
              renderTrigger={this.renderAvoidActionTrigger}
            />
            <FlatButton type="button" active={favorites} onClick={this.handleFavorites}>
              <IconBox size={14}>
                {favorites ? <HearFillIcon /> : <HearLineIcon />}
              </IconBox>
              Only Favorites
            </FlatButton>
          </ModalActions>
        </ModalHeader>
        <ModalBody>
          {
            !data.length &&
            (
              <Center>
                <h4>
                  {
                    !hasMore && !loading ? 'No recipes found' : 'Loading recipes...'
                  }
                </h4>
              </Center>
            )
          }
          {
            data.map(recipe =>
              <Recipe
                mealId={meal.id}
                dishId={dish ? dish.id : null}
                recipe={recipe}
                disabled={loading}
                key={`recipe_${recipe.id}`}
                selected={mealPlans.containsRecipe(meal.id, recipe.id)}
                selectedRecipe={selectedRecipe}
                onPreferenceChoose={this.handlePreferenceUpdate}
                onSelect={mealPlans.addDish}
                onRemove={mealPlans.onRemoveDish}
                onRemoved={this.handleRecipeRemoved}
                onSelectRecipe={onSelectRecipe}
              />
            )
          }
        </ModalBody>
      </InfiniteScroll>
    );
  }

  renderAvoidActionTrigger = () => {
    const LIMIT_TO_SHOW = 2;
    const { filters: { avoid = [] } } = this.state;
    const selected = avoid.slice(0, LIMIT_TO_SHOW);

    let title = AvoidPreferences
      .filter(item => selected.includes(item.value))
      .map(item => item.label)
      .join(', ');

    if (avoid.length > LIMIT_TO_SHOW) {
      title += ', ...';
    }
    title = title || 'Preferences';
    return (
      <FlatButton type="button">
        <IconBox size={14}>
          <SettingsIcon />
        </IconBox>
        { title}
      </FlatButton>
    );
  };

  renderCookingActionTrigger = () => {
    const { filters } = this.state;
    const title = CookingTimes[filters.cookingTime] ?
      `${CookingTimes[filters.cookingTime]} min` :
      'Duration';
    return (
      <FlatButton type="button">
        <IconBox size={14}>
          <TimerIcon />
        </IconBox>
        { title}
      </FlatButton>
    );
  };
}

export default Recipes;
