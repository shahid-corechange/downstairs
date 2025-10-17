import { Box, Icon, Td } from "@chakra-ui/react";
import { Link } from "@inertiajs/react";
import { HiExternalLink } from "react-icons/hi";

interface ExternalLinkProps {
  url: string;
}
const ShowAction = ({ ...props }: ExternalLinkProps) => {
  return (
    <Td w={10} isNumeric>
      <Link href="/dashboard" {...props}>
        <Box
          as="a"
          href="#"
          target="_blank"
          rel="noopener noreferrer"
          display="inline-block"
          ml={1}
        >
          <Icon as={HiExternalLink} boxSize={4} />
        </Box>
      </Link>
    </Td>
  );
};

export default ShowAction;
