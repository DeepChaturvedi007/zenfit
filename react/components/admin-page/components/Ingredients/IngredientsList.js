import React from 'react'
import Card, {
  Header,
  Body,
  Title
} from '../../../shared/components/Card';
import { CenteredText } from '../../../shared/components/Common';
import Button from '../../../shared/components/Button';
import { CardContent } from '../common/Card'
import Preloader from '../common/Preloader'

const normalize = (item) => {
  const {
    amounts,
    addedSugars,
    alcohol,
    allowSplit,
    brand,
    carbohydrates,
    cholesterol,
    deleted,
    excelId,
    fat,
    fiber,
    id,
    kcal,
    kj,
    label,
    monoUnsaturatedFat,
    name,
    names,
    nameDanish,
    polyUnsaturatedFat,
    protein,
    saturatedFat
  } = item;

  return {
    amounts,
    addedSugars,
    alcohol,
    allowSplit,
    brand,
    carbohydrates,
    cholesterol,
    deleted,
    excelId,
    fat,
    fiber,
    id,
    kcal,
    kj,
    label,
    monoUnsaturatedFat,
    name,
    names,
    nameDanish,
    polyUnsaturatedFat,
    protein,
    saturatedFat
  }
};

const Table = (props) => (<table {...props} className="ingredients-table" />);

const TableRow = ({row, onDelete, onEdit}) => {
  const {
    amounts,
    addedSugars,
    alcohol,
    allowSplit,
    brand,
    carbohydrates,
    cholesterol,
    deleted,
    excelId,
    fat,
    fiber,
    id,
    kcal,
    kj,
    label,
    monoUnsaturatedFat,
    name,
    names,
    nameDanish,
    polyUnsaturatedFat,
    protein,
    saturatedFat
  } = row;
  const enName = names.en ? names.en.name: null;
  const nbName = names.nb_NO ? names.nb_NO.name: null;
  const svName = names.sv_SE ? names.sv_SE.name: null;
  const daName = names.da_DK ? names.da_DK.name: null;
  const nlName = names.nl_NL ? names.nl_NL.name: null;
  const fiName = names.fi_FI ? names.fi_FI.name: null;
  const deName = names.de_DE ? names.de_DE.name: null;

  return (
    <tr>
      <td style={{display: "none"}}>
        <input
          name="id"
          type="hidden"
          value={id}
        />
      </td>
      <td>
        <span>{daName}</span>
      </td>
      <td>
        <span>{svName}</span>
      </td>
      <td>
        <span>{nbName}</span>
      </td>
      <td>
        <span>{nlName}</span>
      </td>
      <td>
        <span>{fiName}</span>
      </td>
      <td>
        <span>{deName}</span>
      </td>
      <td>
        <span>{enName}</span>
      </td>
      <td>
        <span>{brand}</span>
      </td>
      <td>
        <span>{carbohydrates}</span>
      </td>
      <td>
        <span>{protein}</span>
      </td>
      <td>
        <span>{fat}</span>
      </td>
      <td>
        <span>{addedSugars}</span>
      </td>
      <td>
        <span>{saturatedFat}</span>
      </td>
      <td>
        <span>{monoUnsaturatedFat}</span>
      </td>
      <td>
        <span>{fiber}</span>
      </td>
      <td>
        <span>{kcal}</span>
      </td>
      <td className="text-right table-actions no-wrap">
        <a
          className="btn btn-default btn-sm btn-edit-plan"
          href="#"
          onClick={()=>onEdit(row)}
        >
          <span className="fa fa-pencil" aria-hidden="true"></span>
        </a>
        <a href="#"
           role="button"
           className="btn btn-default btn-sm"
           onClick={()=>onDelete(row)}
        >
          <i className="fa fa-trash" aria-hidden="true"></i>
        </a>
      </td>
    </tr>
  )
};

const TableHeader = () => {
  const items = [
    'NAME DK',
    'NAME SE',
    'NAME NO',
    'NAME NL',
    'NAME FI',
    'NAME DE',
    'NAME EN',
    'BRAND',
    'CARB',
    'PRO',
    'FAT',
    'SUG',
    'SATFAT',
    'MONFAT',
    'FIBER',
    'KCAL/100G',
    'ACTIONS'
  ];

  return (
    <thead className={'font-bold'}>
      <tr>
        {
          items.map((item, index) =>
          <th key={index}>{item}</th>)
        }
      </tr>
    </thead>
  );
};

const TableBody = ({items, onDelete, onEdit}) => (
  <tbody>
    {
      items
      .map((item) => normalize(item))
      .map((row, i) => <TableRow key={i} row={row} onDelete={onDelete} onEdit={onEdit} />)
    }
  </tbody>
);

const IngredientsList = ({ingredients, loadMore, onSearchChange, onModalOpen, loading, onDelete, onEdit, hasMore}) => {
  const onScrolled = (event) => {
    const el = event.target;
    const shouldLoadMore = el.clientHeight + el.scrollTop === el.scrollHeight;
    if(shouldLoadMore) {
      loadMore();
    }
  };

  return (
    <Card id="ingredients-list" className={'fs-default'}>
      <CardContent onScroll={onScrolled}>
        <Header>
          <div style={{display: "flex", alignItems: "center", flexGrow: "4"}}>
            <Title>Ingredients</Title>
            <Button className="ingredients-table-button" onClick={onModalOpen}>
              <i className="fa fa-plus" aria-hidden="true"/>Add</Button>
          </div>
          <div className="form-search">
            <i className="fa fa-search" aria-hidden="true"/>
            <input type="text"
                   className="form-control"
                   placeholder="Search by name, email or label"
                   onChange={onSearchChange}
            />
           </div>
         </Header>
        <Body>
          {ingredients.length ?
            (
              <Table onScroll={onScrolled}>
                <TableHeader />
                <TableBody items={ingredients}
                           onDelete={onDelete}
                           onEdit={onEdit}
                />
              </Table>
            ) :
            <CenteredText text={'No data'} />
          }
        </Body>
        { (loading && hasMore) && <Preloader /> }
      </CardContent>
    </Card>
  );
};

export default IngredientsList;
