import { Grid, GridItem, Text } from "@chakra-ui/react";

interface InfoRowProps {
  label: string;
  value?: string | JSX.Element;
}
const InfoRow = ({ label, value }: InfoRowProps) => (
  <Grid gridTemplateColumns="repeat(1, 1fr 2fr)" gap={4}>
    <GridItem alignContent="center">
      <Text fontSize="sm">{label}</Text>
    </GridItem>
    <GridItem alignContent="center">
      <Text fontSize="sm">{value || "-"}</Text>
    </GridItem>
  </Grid>
);
export default InfoRow;
