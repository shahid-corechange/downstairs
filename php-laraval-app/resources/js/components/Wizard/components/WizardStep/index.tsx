import { Divider, Flex, Heading, Text } from "@chakra-ui/react";

import { WizardStep as Step } from "../../types";

export interface WizardStepProps extends Step {
  children: React.ReactNode;
}

const WizardStep = ({ title, description, children }: WizardStepProps) => {
  return (
    <Flex flex={1} direction="column" overflowY="auto">
      <Heading size="lg" mb={2}>
        {title}
      </Heading>
      <Text color="gray.500">{description}</Text>
      <Divider mt={4} mb={8} />
      <Flex px={2} flex={1} direction="column" overflowY="auto">
        {children}
      </Flex>
    </Flex>
  );
};

export default WizardStep;
