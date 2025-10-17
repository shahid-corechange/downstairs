const ColorDisplay = (hexColor: string) => {
  const colorStyle = {
    backgroundColor: hexColor,
    width: "40px",
    height: "40px",
    borderRadius: "50%",
  };

  return <div style={colorStyle} />;
};

export default ColorDisplay;
