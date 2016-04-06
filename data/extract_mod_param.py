import json
import sys
import getopt
import simplify_json


def main():
	parameter = load_args()
	source = "simplified.json"
	output = file(parameter + ".json", 'w')

	with open(source) as json_file:
		modules = json.load(json_file)

		for module_code, module_info in modules.iteritems():
			if simplify_json.minify[parameter] in module_info:
				if parameter == "ModuleCredit":
					output.write(module_code + ": \t" + str(module_info[simplify_json.minify[parameter]]) + "\n")
				else:
					output.write(module_code + ": \t" + module_info[simplify_json.minify[parameter]].encode('ascii', 'ignore') + "\n")

	output.close()


def load_args():
	""" Loads arguments into variables

	:return: module_file and semester_file variables
	"""
	parameter = None

	try:
		opts, args = getopt.getopt(sys.argv[1:], 'p:')
	except getopt.GetoptError, err:
		usage()
		sys.exit(2)
	for o, a in opts:
		if o == '-p':
			parameter = a
		else:
			assert False, "unhandled option"
	if parameter is None:
		usage()
		sys.exit(2)
	return parameter


def usage():
	"""Prints the proper format for calling this script."""
	print "usage: " + sys.argv[0] + " -p parameter"


if __name__ == "__main__":
	main()
