import { Grid, GridItem, Text } from "@chakra-ui/react";

interface InfoRowProps {
  label: string;
  value: string | number | undefined;
}

const InfoRow = ({ label, value }: InfoRowProps) => (
  <Grid gridTemplateColumns="repeat(1, 1fr 2fr)" gap={4}>
    <GridItem>
      <Text fontSize="sm" fontWeight="medium">
        {label}
      </Text>
    </GridItem>
    <GridItem>
      <Text fontSize="sm" color={value ? "inherit" : "gray.500"}>
        {value || "-"}
      </Text>
    </GridItem>
  </Grid>
);

export default InfoRow;
