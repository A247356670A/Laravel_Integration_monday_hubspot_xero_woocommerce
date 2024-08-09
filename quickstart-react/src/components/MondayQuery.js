import React, { useEffect } from 'react';
import mondaySdk from 'monday-sdk-js';

const monday = mondaySdk();

const MondayQuery = ({ itemId, columnId, onLocationFetched }) => {

  useEffect(() => {
    const fetchLocation = async () => {
      if (itemId && columnId) { // Ensure itemId and columnId are not null or undefined
        const query = `
          query GetItems($item_id: [ID!], $column_id: String!) {
            items(ids: $item_id) {
              column_values(ids: [$column_id]) {
                id
                value
              }
            }
          }
        `;

        const variables = {
          item_id: itemId,
          column_id: columnId,
        };

        try {
          const response = await monday.api(query, { variables });
          const columnValue = response.data?.items[0]?.column_values[0]?.value;
          onLocationFetched(columnValue); // Pass the fetched location value back to the parent component
        } catch (error) {
          console.error('Error fetching location:', error);
        }
      } else {
        console.error('itemId or columnId not found.');
      }
    };

    fetchLocation();
  }, [itemId, columnId, onLocationFetched]);

  return null;
};

export default MondayQuery;
