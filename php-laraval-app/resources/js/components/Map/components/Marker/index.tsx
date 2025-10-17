import {
  LatLngLiteral,
  Marker as LeafletMarkerType,
  icon as leafletIcon,
} from "leaflet";
import React, { useEffect, useMemo, useRef, useState } from "react";
import {
  Marker as LeafletMarker,
  Popup,
  PopupProps,
  Tooltip,
  TooltipProps,
} from "react-leaflet";

const icon = leafletIcon({
  iconUrl: "/images/marker.png",
  iconSize: [24, 32],
});

export interface MarkerProps {
  position: LatLngLiteral;
  draggable?: boolean;
  popupContainer?: PopupProps;
  popup?: React.ReactNode;
  tooltipContainer?: TooltipProps;
  tooltip?: React.ReactNode;
  onDragEnd?: (position: LatLngLiteral) => void;
}

const Marker = ({
  position: center,
  draggable,
  popupContainer,
  popup,
  tooltipContainer,
  tooltip,
  onDragEnd,
}: MarkerProps) => {
  const [position, setPosition] = useState(center);
  const ref = useRef<LeafletMarkerType>(null);

  const eventHandlers = useMemo(
    () => ({
      dragend() {
        if (ref.current && draggable) {
          setPosition(ref.current.getLatLng());
          onDragEnd?.(ref.current.getLatLng());
        }
      },
    }),
    [draggable, onDragEnd],
  );

  useEffect(() => {
    setPosition(center);
  }, [center]);

  return (
    <LeafletMarker
      ref={ref}
      position={position}
      draggable={draggable}
      eventHandlers={eventHandlers}
      icon={icon}
    >
      {popup && <Popup {...popupContainer}>{popup}</Popup>}
      {tooltip && <Tooltip {...tooltipContainer}>{tooltip}</Tooltip>}
    </LeafletMarker>
  );
};

export default Marker;
