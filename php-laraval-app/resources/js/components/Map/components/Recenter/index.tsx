import { useMap } from "react-leaflet";

interface RecenterProps {
  latitude: number;
  longitude: number;
}

const Recenter = ({ latitude, longitude }: RecenterProps) => {
  const map = useMap();
  map.setView({ lat: latitude, lng: longitude }, map.getZoom());
  return null;
};

export default Recenter;
