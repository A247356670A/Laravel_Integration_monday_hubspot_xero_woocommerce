import React from "react";
import { APIProvider, Map, AdvancedMarker } from '@vis.gl/react-google-maps';
import MondayQuery from "./components/MondayQuery";
import { useState, useEffect } from "react";
import "./App.css";
import mondaySdk from "monday-sdk-js";
import "monday-ui-react-core/dist/main.css";

const monday = mondaySdk();

const App = () => {
  const [context, setContext] = useState();
  const [setting, setSetting] = useState();
  const [location, setLocation] = useState(null);

  useEffect(() => {
    monday.execute("valueCreatedForUser");
    const callback = res => {
      if (res.type === "context") {
        setContext(res.data);
        console.log("Context received: ", res.data);
      } else if (res.type === "settings") {
        setSetting(res.data);
        console.log("Settings received: ", res.data);
      }
    }
    monday.listen(['settings', 'context'], callback);
  }, []);
  const handleLocationFetched = (locationValue) => {
    console.log("got locationvalue: " + locationValue);
    const { lat, lng } = JSON.parse(locationValue);
    setLocation({ lat: parseFloat(lat), lng: parseFloat(lng) });
  };
  const locationColumnKeys = setting && setting.location_column ? Object.keys(setting.location_column) : [];
  const boardId = context ? context.boardId : "Loading boardId...";
  const itemId = context ? context.itemId : "Loading itemId...";
  const locationColumnKey = locationColumnKeys.length > 0 ? locationColumnKeys[0] : "No location column found";

  const attentionBoxText = "Hello, your user_id is: " +
    (context ? context.user.id : "still loading") +
    ".\n\n" +
    "Location Column Keys: " + locationColumnKey + "\n" +
    "Board Id: " + boardId + "\n" +
    "Item Id: " + itemId;

  return (
    <div>
      <h1>Google Maps Address Display</h1>
      <p>{attentionBoxText}</p>
      {boardId && itemId && locationColumnKey ? (
        <MondayQuery itemId={itemId} columnId={locationColumnKey} onLocationFetched={handleLocationFetched} />
      ) : (
        <p>Waiting for boardId, itemId, and locationColumnKey...</p>
      )}
      {location ? 
      <div style={{width: 500, height: 500}} >
        <APIProvider apiKey={''}>
          <Map zoom={15} center={location} />
        </APIProvider>
      </div>
      : <p>Loading location...</p>}
    </div>

  );
};

export default App;
