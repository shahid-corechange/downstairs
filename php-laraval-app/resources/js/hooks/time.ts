import dayjs from "dayjs";
import plugin from "dayjs/plugin/duration";
import { useEffect, useRef, useState } from "react";

interface UseTimerOptions {
  value: number;
  unit: plugin.DurationUnitType;
  shouldStart?: boolean;
}

export const useTimer = ({
  value,
  unit,
  shouldStart = false,
}: UseTimerOptions) => {
  const [duration, setDuration] = useState(dayjs.duration(0));
  const interval = useRef<number>();

  const start = () => {
    let newDuration = dayjs.duration(value, unit);
    setDuration(newDuration);

    interval.current = window.setInterval(() => {
      if (newDuration.asSeconds() <= 0) {
        clearInterval(interval.current);
        return;
      }

      newDuration = newDuration.subtract(1, "second");
      setDuration(newDuration);
    }, 1000);
  };

  const stop = () => {
    clearInterval(interval.current);
  };

  const restart = () => {
    stop();
    start();
  };

  useEffect(() => {
    if (shouldStart) {
      start();
    }

    return () => {
      stop();
    };
  }, [shouldStart]);

  return { duration, start, stop, restart };
};
