import React from 'react';

export const TableHeader = props => <thead {...props} />;
export const TableBody = props => <tbody {...props} />;
export const TableFooter = props => <tfoot {...props} />;
export const TableRow = props => <tr {...props} />;
export const TableColumn = ({mode, ...props}) => {
  switch (mode) {
    case 'th':
      return <th {...props} />;
    case 'td':
    default:
      return <td {...props} />;
  }
};

const Table = props => <table className="table table-hover no-margins" {...props} />;

export default Table;