import React, {Fragment, useState, useEffect} from 'react';
import { createIngredient, fetchIngredients, updateIngredient, deleteIngredient } from '../api/ingredients';
import IngredientsList from '../components/Ingredients/IngredientsList';
import IngredientsModal from '../components/Ingredients/IngredientsModal';

let timer = 0;

const Ingredients = (props) => {
  const [showModal, setShowModal] = useState(false);
  const [loading, setLoading] = useState(false);
  const [hasMore, setHasMore] = useState(true);
  const [ingredients, setIngredients] = useState([]);
  const [ingredient, setIngredient] = useState({});

  const [filters, setFilters] = useState({
    limit: 20,
    offset: 0,
    query: '',
  });

  useEffect(() => {
    setLoading(false);
    fetchIngredients(filters)
      .then(items => {
        setIngredients([...ingredients, ...items]);
        setHasMore(filters.limit === items.length);
      })
      .catch(error => {
        alert(error.message)
      })
      .finally(() => {
          setLoading(true);
      })
  }, [filters])

  const handleSubmit = (data) => {
    if (data.id) {
      return updateIngredient(data)
        .then(data => {
          setIngredients(ingredients.map(ingredient => ingredient.id === data.id
            ? { ...ingredient, ...data }
            : ingredient));
          setIngredient(data);
          return data;
        });
    } else {
      return createIngredient(data)
        .then(data => {
          const list = [...ingredients].reverse()
          list.push(data)
          setIngredients(list.reverse());
          return data;
        }
      );
    }
  };

  const handleLoadMore = () => {
    if (hasMore) {
      setFilters({ ...filters, offset: ingredients.length })
    }
  }

  const handleSearchChange = (event) => {
    const { value } = event.target;
    clearTimeout(timer);
    timer = setTimeout(() => {
      setIngredients([])
      setFilters({ ...filters, offset: 0, query: value })
    }, 600);
  }

  const handleModalOpen = (event) => {
    event.preventDefault();
    setIngredient({});
    setShowModal(true);
  };

  const handleDelete = (item) => {
    deleteIngredient(item.id)
      .then(() => {
        setIngredients(ingredients.filter(ingredient => ingredient.id !== item.id));
      })
      .catch(error => {
        alert(error.message)
      })
  };

  const handleEdit = (item) => {
    setIngredient(item);
    setShowModal(true);
  };

  return (
      <Fragment>
        <IngredientsList ingredients={ingredients}
                         loadMore={handleLoadMore}
                         onSearchChange={handleSearchChange}
                         onModalOpen={handleModalOpen}
                         onDeleteIngredient={handleModalOpen}
                         onEditIngredient={handleModalOpen}
                         loading={loading}
                         onEdit={handleEdit}
                         onDelete={handleDelete}
                         hasMore={hasMore}
        />
        <IngredientsModal show={showModal}
                          onHide={() => setShowModal(false)}
                          onSubmit={handleSubmit}
                          ingredient={ingredient}
        />
      </Fragment>
    );
};

export default Ingredients;
